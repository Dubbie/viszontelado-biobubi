<?php

namespace App\Subesz;


use App\BundleProduct;
use App\Product;
use App\Stock;
use App\StockHistory;
use App\User;
use Illuminate\Mail\Message;

class StockService
{
    /**
     * Visszaadja a csomag termékeket.
     *
     * @return Product[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getBundles() {
        return Product::has('subProducts')->get();
    }

    /**
     * Visszaadja az alap termékeket
     *
     * @return Product[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getBaseProducts() {
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

    public function subtractStockFromOrder(array $products, User $reseller)
    {
//        // Először összerakjuk, hogy miket kell majd levonni
//        $stockList = [];
//        foreach ($products as $product) {
//            $stockParts = $this->getPartsFromSku($product->sku);
//            if (!$stockParts) {
//                return false;
//            }
//
//            $stockList[] = $stockParts;
//        }
//
//        dd($stockList);
//        // Most, megnézzük, hogy letudjuk-e vonni
//        foreach ($stockList as $itemSku) {
//            $this->bookStock($reseller, $itemSku);
//        }
    }

    public function bookStock(User $reseller, $sku, $count = 1)
    {
        $stockItem = $reseller->stock()->where('sku', $sku)->first();
        if (!$stockItem) {
            $stockItem = new Stock();
            $stockItem->user_id = $reseller->id;
            $stockItem->sku = $sku;
            $stockItem->inventory_on_hand = -1;
            $stockItem->inventory_booked = $count;
        } else {
            $stockItem->inventory_on_hand -= $count;
            $stockItem->inventory_booked += $count;
        }
    }
}