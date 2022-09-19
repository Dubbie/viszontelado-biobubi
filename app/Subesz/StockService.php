<?php

namespace App\Subesz;

use App\Product;
use App\Stock;
use App\StockMovement;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class StockService
{
    /**
     * Visszaadja az alap termékeket. Az alap termék olyan termék, amelyhez nem tartoznak altermékek (subProducts) és jelenleg a status mező értéke Igaz.
     *
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function getBaseProducts(): Collection|array {
        return Product::doesntHave('subProducts')->where('status', '=', '1')->get();
    }

    /**
     * Visszaadja a központi készlet darabszámát az adott cikkszámra.
     *
     * @param        $sku
     * @return int
     */
    public function getCentralStockOnHand($sku): int {
        $stockEntry = Stock::where([
            ['user_id', '=', null],
            ['sku', '=', $sku],
        ])->first();

        if ($stockEntry) {
            return $stockEntry->inventory_on_hand;
        } else {
            return 0;
        }
    }

    /**
     * Visszaadja a központi készlet összértékét (Nagyker árból)
     *
     * @param  bool  $formatted
     * @return float|int|string
     */
    public function getCentralStockValue(bool $formatted = false): float|int|string {
        /** @var Stock $cs */
        $sum = 0;
        foreach (Stock::where('user_id', null)->get() as $cs) {
            $sum += ($cs->product->wholesale_price * $cs->inventory_on_hand);
        }

        if ($formatted) {
            return number_format($sum, 0, '.', ' ').' Ft';
        }

        return $sum;
    }

    /**
     * Visszaadja a központi készletnél látható listát.
     *
     * @return string
     */
    public function getCentralStockHTML(): string {
        return view('inc.stock.central-list')->with([
            'stock' => Stock::where('user_id', null)->get(),
        ])->toHtml();
    }

    /**
     * Hozzáaadja a megadott cikkszámból a megfelelő mennyiséget.
     *
     * @param $sku
     * @param $qty
     * @return \App\Stock
     */
    public function addToCentralStock($sku, $qty): Stock {
        $product = Product::where('sku', $sku)->first();
        if (! $product) {
            Log::error('Nincs ilyen cikkszámú termék, hogy hozzáadjuk a központi készlethez! (Cikkszám: '.$sku.')');

            return false;
        }

        /** @var Stock $cs */
        $cs = Stock::where([
            ['sku', '=', $sku],
            ['user_id', '=', null],
        ])->first();

        $oldInventory = 0;
        if (! $cs) {
            $cs                    = new Stock();
            $cs->sku               = $sku;
            $cs->inventory_on_hand = $qty;
        } else {
            $oldInventory          = $cs->inventory_on_hand;
            $cs->inventory_on_hand += $qty;
        }

        $cs->save();
        Log::info('Központi készlet frissítve.');
        Log::info(sprintf(' - %s (%s)', $sku, ($qty > 0 ? '+'.$qty : $qty)));

        // 2. Elmentjük a mozgást
        $movement                  = new StockMovement();
        $movement->product_sku     = $sku;
        $movement->user_id         = null;
        $movement->gross_price     = $product->gross_price;
        $movement->wholesale_price = $product->wholesale_price;
        $movement->purchase_price  = $product->purchase_price;
        $movement->quantity        = $cs->inventory_on_hand - $oldInventory;
        $movement->save();
        Log::info(sprintf('A központ kapott %s db %s terméket (Cikkszám: %s).', $qty, $product->name, $product->sku));

        return $cs;
    }

    /**
     * @param  User  $recipient
     * @param        $sku
     * @param        $count
     * @return bool
     */
    public function addToStock(User $recipient, $sku, $count): bool {
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

        // 2. Elmentjük a mozgást a viszonteladónak
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
        $cs                    = Stock::where([
            ['user_id', '=', null],
            ['sku', '=', $sku],
        ])->first();
        $cs->inventory_on_hand -= $count;
        $cs->save();
        Log::info(sprintf('A központi készletből levonásra került %s db %s termék (Cikkszám: %s)', $count, $product->name, $product->sku));

        // 4. Elmentjük a mozgást
        $movement                  = new StockMovement();
        $movement->product_sku     = $sku;
        $movement->user_id         = null;
        $movement->gross_price     = $product->gross_price;
        $movement->wholesale_price = $product->wholesale_price;
        $movement->purchase_price  = $product->purchase_price;
        $movement->quantity        = -1 * $count;
        $movement->save();

        /*
        // 4. Hozzáadjuk a központnak, mint bevétel
        $amount = $count * $product->wholesale_price;
        $revenueService->storeCentralIncome('Készletértékesítés', null, $amount, null, sprintf('Készlet átadva %s viszonteladónak.', $recipient->name));

        // 5. Hozzáadjuk a viszonteladónak, mint kiadás
        $revenueService->storeResellerExpense('Készletvásárlás', $amount, $recipient, null, sprintf('%s db %s', $count, $product->name));
        */

        return true;
    }

    /**
     * Visszaadja a csomag termékeket.
     *
     * @return Product[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getBundles(): Collection|array {
        return Product::has('subProducts')->get();
    }

    /**
     * @param $arrSku
     * @param $arrCount
     * @return array
     */
    public function getProductDataFromInput($arrSku, $arrCount): array {
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
     * Visszaadja cikkszám alapján az elmentett terméket.
     *
     * @param $sku
     * @return Product|Product[]|bool|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function getLocalProductBySku($sku): Model|Collection|array|Product|bool|null {
        $product = Product::find($sku);

        if (! $product) {
            Log::error('---- Hiba a megrendelés termékeinek átalakításakor ----');
            Log::error(sprintf('-- Nem található ilyen cikkszámú termék (Cikkszám: "%s") --', $sku));

            return false;
        }

        return $product;
    }
}