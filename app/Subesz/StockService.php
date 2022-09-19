<?php

namespace App\Subesz;

use App\CentralStock;
use App\Order;
use App\Product;
use App\Stock;
use App\StockMovement;
use App\User;
use Log;

class StockService
{
    /**
     * @param  User  $recipient
     * @param  int   $stockId
     * @param        $newInventory
     */
    public function updateStock(User $recipient, int $stockId, $newInventory) {
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
    public function bookOrder(array $skuList, $orderId): bool {
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
     * @param $sku
     * @return Product|Product[]|bool|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function getLocalProductBySku($sku) {
        $product = Product::find($sku);

        if (! $product) {
            Log::error('---- Hiba a megrendelés termékeinek átalakításakor ----');
            Log::error(sprintf('-- Nem található ilyen cikkszámú termék (Cikkszám: "%s") --', $sku));

            return false;
        }

        return $product;
    }

    /**
     * Létrehozza a megrendeléshez tartozó termékeket.
     *
     * @param $orderId
     * @return bool
     */
    public function subtractStockFromOrder($orderId): bool {
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
     * @param $userId
     * @return string
     */
    public function getResellerStockListHTML($userId): string {
        return view('inc.stock.reseller-stock-list')->with([
            'reseller' => User::find($userId),
        ])->toHtml();
    }

    /**
     * @param  bool  $first
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function getCentralStockRow($first = false) {
        return view('inc.stock.cs-row')->with([
            'products' => resolve('App\Subesz\StockService')->getBaseProducts(),
            'first'    => $first,
        ])->toHtml();
    }

    /**
     * Visszaadja az alap termékeket
     *
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function getBaseProducts(): \Illuminate\Database\Eloquent\Collection|array {
        return Product::doesntHave('subProducts')->where('status', '=', '1')->get();
    }

    /**
     * @param  bool  $first
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function getResellerStockRow($first = false) {
        return view('inc.stock.rs-row')->with([
            'centralStock' => CentralStock::all(),
            'first'        => $first,
        ])->toHtml();
    }
}
