<?php

namespace App\Subesz;


use App\BundleProduct;
use App\Order;
use App\OrderProducts;
use App\Product;
use App\Stock;
use App\StockHistory;
use App\User;
use Illuminate\Mail\Message;
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
        return Product::doesntHave('subProducts')->get();
    }

    /**
     * @param $arrSku
     * @param $arrCount
     * @return array
     */
    public function getProductDataFromInput($arrSku, $arrCount)
    {
        $stockData = [];

        foreach ($arrSku as $key => $sku) {
            $count = intval(str_replace(' ', '', $arrCount[$key]));

            $stockIndex = array_search($sku, array_column($stockData, 'sku'));
            if ($stockIndex !== false) {
                $stockData[$stockIndex]['count'] += $count;
            } else {
                $stockData[] = [
                    'sku' => $sku,
                    'count' => $count
                ];
            }
        }

        return $stockData;
    }

    /**
     * @param User $recipient
     * @param User $sender
     * @param $sku
     * @param $count
     */
    public function addToStock(User $recipient, User $sender, $sku, $count)
    {
        // 1. Megnézzük, hogy van-e már ilyen termékből készlete
        $stockItem = $recipient->stock()->where('sku', $sku)->first() ?? new Stock();
        $oldInventory = $stockItem->inventory_on_hand ?? 0; // Elmentjük, ha volt régi készlete

        $stockItem->user_id = $recipient->id;
        $stockItem->sku = $sku;
        $stockItem->inventory_on_hand += $count;
        $stockItem->save();

        $history = new StockHistory();
        $history->recipient = $recipient->id;
        $history->sender = $sender->id;
        $history->sku = $sku;
        $history->name = $stockItem->product->name;
        $history->amount = $stockItem->inventory_on_hand - $oldInventory;
        $history->save();
    }

    /**
     * @param User $recipient
     * @param User $sender
     * @param int $stockId
     * @param $newInventory
     */
    public function updateStock(User $recipient, User $sender, int $stockId, $newInventory)
    {
        /** @var Stock $stockItem */
        $oldInventory = null;
        $stockItem = $recipient->stock()->find($stockId);

        // Elmentjük a régi állást
        $oldInventory = $stockItem->inventory_on_hand;

        // Frissítjük az újra
        $stockItem->inventory_on_hand = $newInventory;
        $stockItem->save();

        // Létrehozzuk az új eseményt
        $history = new StockHistory();
        $history->recipient = $recipient->id;
        $history->sender = $sender->id;
        $history->sku = $stockItem->sku;
        $history->name = $stockItem->product->name;
        $history->amount = $newInventory - $oldInventory;
        $history->save();
    }

    /**
     * Létrehozza a megrendeléshez tartozó termékeket.
     *
     * @param array $skuList
     * @param $orderId
     * @return bool
     */
    public function bookOrder(array $skuList, $orderId)
    {
        /** @var User $reseller */
        $localOrder = Order::find($orderId);
        $reseller = $localOrder->getReseller()['correct'];

        \Log::info('-- Új megrendelés készletének levezetése: --');
        \Log::info('-- -- Viszonteladó: ' . $reseller->name);
        \Log::info('-- -- Megrendelt termékek: ');
        foreach ($skuList as $orderedProduct) {
            // Kikeressük, hogy mi is ez a termék nálunk
            $localProduct = $this->getLocalProductBySku($orderedProduct['sku']);
            if (!$localProduct) {
                $sp = resolve('App\Subesz\ShoprenterService');
                $sp->updateProducts();

                $localProduct = $this->getLocalProductBySku($orderedProduct['sku']);
                if (!$localProduct) {
                    return false;
                }
            }

            // Kiszejdük, hogy ez a termék mikből áll
            \Log::info(sprintf('-- -- %s (Cikkszám: %s) ami a következőkből áll:', $localProduct->name, $localProduct->sku));
            foreach ($localProduct->getSubProducts() as $subProduct) {
                /** @var Product $baseProduct */
                $baseProduct = $subProduct['product'];
                $baseProductCount = $subProduct['count'];

                \Log::info(sprintf('-- -- -- Alaptermék: %s db, %s (Cikkszám: %s)', $baseProductCount, $baseProduct->name, $baseProduct->sku));
                /** @var Stock $stockItem */
                $stockItem = $reseller->stock()->where('sku', $baseProduct->sku)->first();
                // Ha nincs még, akkor létrehozzuk
                if (!$stockItem) {
                    \Log::info('-- -- -- A viszonteladónak még nincs ilyen termékből készlete, ezért létrehozzuk.');
                    $stockItem = new Stock();
                    $stockItem->sku = $baseProduct->sku;
                    $stockItem->inventory_on_hand = 0;
                    $stockItem->user_id = $reseller->id;

                    // Elmentsük
                    $stockItem->save();
                }

                \Log::info('-- -- -- Készlet frissítve.');
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
    public function subtractStockFromOrder($orderId)
    {
        /** @var User $reseller */
        $localOrder = Order::find($orderId);
        $reseller = $localOrder->reseller;

        \Log::info('-- Megrendelés teljesítve, készletének levezetése: --');
        \Log::info('-- -- Viszonteladó: ' . $reseller->name);
        \Log::info('-- -- Megrendelt termékek: ');
        foreach ($localOrder->products as $orderedProduct) {
            $localProduct = $orderedProduct->product;

            // Kiszejdük, hogy ez a termék mikből áll
            \Log::info(sprintf('-- -- %s (Cikkszám: %s) ami a következőkből áll:', $localProduct->name, $localProduct->sku));
            foreach ($localProduct->getSubProducts() as $subProduct) {
                /** @var Product $baseProduct */
                $baseProduct = $subProduct['product'];
                $baseProductCount = $subProduct['count'];

                \Log::info(sprintf('-- -- -- Alaptermék: %s db, %s (Cikkszám: %s)', $baseProductCount, $baseProduct->name, $baseProduct->sku));
                /** @var Stock $stockItem */
                $stockItem = $reseller->stock()->where('sku', $baseProduct->sku)->first();
                // Ha nincs még, akkor létrehozzuk
                if (!$stockItem) {
                    \Log::info('-- -- -- A viszonteladónak még nincs ilyen termékből készlete, ezért létrehozzuk.');
                    $stockItem = new Stock();
                    $stockItem->sku = $baseProduct->sku;
                    $stockItem->inventory_on_hand = -1 * ($orderedProduct->product_qty * $baseProductCount); // Megrendelt termék mennyiség (pl.: 3db 3 liter mosószer csomag, akkor 3 * 3)
                    $stockItem->user_id = $reseller->id;
                } else {
                    $stockItem->inventory_on_hand = $stockItem->inventory_on_hand - ($orderedProduct->product_qty * $baseProductCount);
                }

                // Elmentsük
                $stockItem->save();
                \Log::info('-- -- -- Készlet frissítve.');
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

        if (!$product) {
            \Log::error('---- Hiba a megrendelés termékeinek átalakításakor ----');
            \Log::error(sprintf('-- Nem található ilyen cikkszámú termék (Cikkszám: "%s") --', $sku));
            return false;
        }

        return $product;
    }
}