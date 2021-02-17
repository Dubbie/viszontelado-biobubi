<?php

namespace App\Subesz;

use App\BundleProduct;
use App\CentralStock;
use App\Income;
use App\Order;
use App\OrderProducts;
use App\Product;
use App\Stock;
use App\StockMovement;
use App\User;
use Illuminate\Mail\Message;
use Log;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class StockService
{
    /**
     * Visszaadja a csomag termékeket.
     *
     * @return Product[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getBundles()
    {
        return Product::has('subProducts')->get();
    }

    /**
     * Visszaadja az alap termékeket
     *
     * @return Product[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getBaseProducts()
    {
        return Product::doesntHave('subProducts')->where('status', '==','1')->get();
    }

    /**
     * @param $arrSku
     * @param $arrCount
     * @return array
     */
    public function getProductDataFromInput($arrSku, $arrCount): array
    {
        $stockData = [];

        foreach ($arrSku as $key => $sku) {
            $count = intval(str_replace(' ', '', $arrCount[$key]));

            $stockIndex = array_search($sku, array_column($stockData, 'sku'));
            if ($stockIndex !== false) {
                $stockData[$stockIndex]['count'] += $count;
            } else {
                $stockData[] = [
                    'sku'   => $sku,
                    'count' => $count,
                ];
            }
        }

        return $stockData;
    }

    /**
     * @param  User  $recipient
     * @param        $sku
     * @param        $count
     * @return bool
     */
    public function addToStock(User $recipient, $sku, $count): bool
    {
        $revenueService = resolve('App\Subesz\RevenueService');

        $product = Product::find($sku);
        if (! $product) {
            return false;
        }

        // 1. Megnézzük, hogy van-e már ilyen termékből készlete
        $stockItem                    = $recipient->stock()->where('sku', $sku)->first() ?? new Stock();
        $oldInventory                 = $stockItem->inventory_on_hand ?? 0; // Elmentjük, ha volt régi készlete
        $stockItem->user_id           = $recipient->id;
        $stockItem->sku               = $sku;
        $stockItem->inventory_on_hand += $count;
        $stockItem->save();

        // 2. Elmentjük a mozgást
        $movement                  = new StockMovement();
        $movement->product_sku     = $sku;
        $movement->user_id         = $recipient->id;
        $movement->gross_price     = $product->gross_price;
        $movement->wholesale_price = $product->wholesale_price;
        $movement->purchase_price  = $product->purchase_price;
        $movement->quantity        = $stockItem->inventory_on_hand - $oldInventory;
        $movement->save();
        Log::info(sprintf('%s viszonteladó kapott %s db %s terméket (Cikkszám: %s) a központtól.', $recipient->name, $count, $product->name, $product->sku));

        // 3. Levonjuk a központi készletből
        /** @var CentralStock $cs */
        $cs                    = CentralStock::where('product_sku', $sku)->first();
        $cs->inventory_on_hand -= $count;
        $cs->save();
        Log::info(sprintf('A központi készletből levonásra került %s db %s termék (Cikkszám: %s)', $count, $product->name, $product->sku));

        // 4. Hozzáadjuk a központnak, mint bevétel
        $amount = $count * $product->wholesale_price;
        $revenueService->storeCentralIncome('Készletértékesítés', null, $amount, null, sprintf('Készlet átadva %s viszonteladónak.', $recipient->name));

        // 5. Hozzáadjuk a viszonteladónak, mint kiadás
        $revenueService->storeResellerExpense('Készletvásárlás', $amount, $recipient, null, sprintf('%s db %s', $count, $product->name));

        return true;
    }

    /**
     * @param  User  $recipient
     * @param  int   $stockId
     * @param        $newInventory
     */
    public function updateStock(User $recipient, int $stockId, $newInventory)
    {
        /** @var Stock $stockItem */
        $oldInventory = null;
        $stockItem    = $recipient->stock()->find($stockId);

        // Elmentjük a régi állást
        $oldInventory = $stockItem->inventory_on_hand;

        // Frissítjük az újra
        $stockItem->inventory_on_hand = $newInventory;
        $stockItem->save();

        // Létrehozzuk az új eseményt
        $mvmt                  = new StockMovement();
        $mvmt->user_id         = $recipient->id;
        $mvmt->product_sku     = $stockItem->sku;
        $mvmt->quantity        = ($newInventory - $oldInventory);
        $mvmt->gross_price     = $stockItem->product->gross_price;
        $mvmt->purchase_price  = $stockItem->product->purchase_price;
        $mvmt->wholesale_price = $stockItem->product->wholesale_price;
        $mvmt->save();
    }

    /**
     * Létrehozza a megrendeléshez tartozó termékeket.
     *
     * @param  array  $skuList
     * @param         $orderId
     * @return bool
     */
    public function bookOrder(array $skuList, $orderId): bool
    {
        /** @var User $reseller */
        $localOrder = Order::find($orderId);
        $reseller   = $localOrder->getReseller()['correct'];

        Log::info('-- Új megrendelés készletének levezetése: --');
        Log::info('-- -- Viszonteladó: '.$reseller->name);
        Log::info('-- -- Megrendelt termékek: ');
        foreach ($skuList as $orderedProduct) {
            // Kikeressük, hogy mi is ez a termék nálunk
            $localProduct = $this->getLocalProductBySku($orderedProduct['sku']);
            if (! $localProduct) {
                $sp = resolve('App\Subesz\ShoprenterService');
                $sp->updateProducts();

                $localProduct = $this->getLocalProductBySku($orderedProduct['sku']);
                if (! $localProduct) {
                    return false;
                }
            }

            // Kiszejdük, hogy ez a termék mikből áll
            Log::info(sprintf('-- -- %s (Cikkszám: %s) ami a következőkből áll:', $localProduct->name, $localProduct->sku));
            foreach ($localProduct->getSubProducts() as $subProduct) {
                /** @var Product $baseProduct */
                $baseProduct      = $subProduct['product'];
                $baseProductCount = $subProduct['count'];

                Log::info(sprintf('-- -- -- Alaptermék: %s db, %s (Cikkszám: %s)', $baseProductCount, $baseProduct->name, $baseProduct->sku));
                /** @var Stock $stockItem */
                $stockItem = $reseller->stock()->where('sku', $baseProduct->sku)->first();
                // Ha nincs még, akkor létrehozzuk
                if (! $stockItem) {
                    Log::info('-- -- -- A viszonteladónak még nincs ilyen termékből készlete, ezért létrehozzuk.');
                    $stockItem                    = new Stock();
                    $stockItem->sku               = $baseProduct->sku;
                    $stockItem->inventory_on_hand = 0;
                    $stockItem->user_id           = $reseller->id;

                    // Elmentsük
                    $stockItem->save();
                }

                Log::info('-- -- -- Készlet frissítve.');
            }
        }

        return true;
    }

    /**
     * Létrehozza a megrendeléshez tartozó termékeket.
     *
     * @param $orderId
     * @return bool
     */
    public function subtractStockFromOrder($orderId): bool
    {
        /** @var User $reseller */
        $localOrder = Order::find($orderId);
        $reseller   = $localOrder->reseller;

        Log::info('-- Megrendelés teljesítve, készletének levezetése: --');
        Log::info('-- -- Viszonteladó: '.$reseller->name);
        Log::info('-- -- Megrendelt termékek: ');
        foreach ($localOrder->products as $orderedProduct) {
            $localProduct = $orderedProduct->product;

            // Kiszejdük, hogy ez a termék mikből áll
            Log::info(sprintf('-- -- %s (Cikkszám: %s) ami a következőkből áll:', $localProduct->name, $localProduct->sku));
            foreach ($localProduct->getSubProducts() as $subProduct) {
                /** @var Product $baseProduct */
                $baseProduct      = $subProduct['product'];
                $baseProductCount = $subProduct['count'];

                Log::info(sprintf('-- -- -- Alaptermék: %s db, %s (Cikkszám: %s)', $baseProductCount, $baseProduct->name, $baseProduct->sku));
                /** @var Stock $stockItem */
                $stockItem = $reseller->stock()->where('sku', $baseProduct->sku)->first();
                // Ha nincs még, akkor létrehozzuk
                if (! $stockItem) {
                    Log::info('-- -- -- A viszonteladónak még nincs ilyen termékből készlete, ezért létrehozzuk.');
                    $stockItem                    = new Stock();
                    $stockItem->sku               = $baseProduct->sku;
                    $stockItem->inventory_on_hand = -1 * ($orderedProduct->product_qty * $baseProductCount); // Megrendelt termék mennyiség (pl.: 3db 3 liter mosószer csomag, akkor 3 * 3)
                    $stockItem->user_id           = $reseller->id;
                } else {
                    $stockItem->inventory_on_hand = $stockItem->inventory_on_hand - ($orderedProduct->product_qty * $baseProductCount);
                }

                // Elmentsük
                $stockItem->save();
                Log::info('-- -- -- Készlet frissítve.');
            }
        }

        return true;
    }

    /**
     * @param $sku
     * @return Product|Product[]|bool|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function getLocalProductBySku($sku)
    {
        $product = Product::find($sku);

        if (! $product) {
            Log::error('---- Hiba a megrendelés termékeinek átalakításakor ----');
            Log::error(sprintf('-- Nem található ilyen cikkszámú termék (Cikkszám: "%s") --', $sku));

            return false;
        }

        return $product;
    }

    /**
     * @return string
     */
    public function getCentralStockHTML(): string
    {
        return view('inc.stock.central-list')->with([
            'centralStock' => CentralStock::all(),
        ])->toHtml();
    }

    /**
     * @param $userId
     * @return string
     */
    public function getResellerStockListHTML($userId): string
    {
        return view('inc.stock.reseller-stock-list')->with([
            'reseller' => User::find($userId),
        ])->toHtml();
    }

    /**
     * @param  bool  $first
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function getCentralStockRow($first = false)
    {
        return view('inc.stock.cs-row')->with([
            'products' => resolve('App\Subesz\StockService')->getBaseProducts(),
            'first'    => $first,
        ])->toHtml();
    }

    /**
     * @param  bool  $first
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function getResellerStockRow($first = false)
    {
        return view('inc.stock.rs-row')->with([
            'centralStock' => CentralStock::all(),
            'first'        => $first,
        ])->toHtml();
    }

    /**
     * @param $sku
     * @param $qty
     * @return CentralStock
     */
    public function addToCentralStock($sku, $qty): CentralStock
    {
        /** @var CentralStock $cs */
        $cs = CentralStock::where('product_sku', $sku)->first();
        if (! $cs) {
            $cs                    = new CentralStock();
            $cs->product_sku       = $sku;
            $cs->inventory_on_hand = $qty;
        } else {
            $cs->inventory_on_hand += $qty;
        }

        $cs->save();
        Log::info('Központi készlet frissítve.');
        Log::info(sprintf(' - %s (%s)', $sku, ($qty > 0 ? '+'.$qty : $qty)));

        return $cs;
    }

    /**
     * @param  bool  $formatted
     * @return float|int|string
     */
    public function getCentralStockValue($formatted = false)
    {
        /** @var CentralStock $cs */
        $sum = 0;
        foreach (CentralStock::all() as $cs) {
            $sum += ($cs->product->wholesale_price * $cs->inventory_on_hand);
        }

        if ($formatted) {
            return number_format($sum, 0, '.', ' ').' Ft';
        }

        return $sum;
    }
}
