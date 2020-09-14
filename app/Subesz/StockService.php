<?php

namespace App\Subesz;


use App\Stock;
use App\StockHistory;
use App\User;
use Illuminate\Mail\Message;

class StockService
{
    private $skuMap;

    public function __construct()
    {
        $this->skuMap = [
            '1' => ['19', '15'],
            'CEM3' => ['CEM1', 'CEM1', 'CEM1'],
            'CEFSZ3' => ['CEFSZ1', 'CEFSZ1', 'CEFSZ1'],
            'CEF3' => ['CEF1', 'CEF1', 'CEF1'],
            'CEFMSZB' => ['CEM1', 'CEFSZ1', 'CEF1', '19'],
            'CEFMSZ' => ['CEM1', 'CEFSZ1', 'CEF1'],
            '11' => ['5', '5', '5'],
            'CEM1' => 'CEM1',
            'CEFSZ1' => 'CEFSZ1',
            'CEF1' => 'CEF1',
            '19' => '19',
            '15' => '15',
            '2' => '2',
            '5' => '5',
        ];
    }

    /**
     * @param $arrSku
     * @param $arrCount
     * @return array
     */
    public function getStockDataFromInput($arrSku, $arrCount)
    {
        $stockData = [];

        foreach ($arrSku as $key => $item) {
            $split = explode('|', $item);
            $sku = $split[0];
            $name = $split[1];
            $count = intval(str_replace(' ', '', $arrCount[$key]));

            $stockIndex = array_search($sku, array_column($stockData, 'sku'));
            if ($stockIndex !== false) {
                $stockData[$stockIndex]['count'] += $count;
            } else {
                $stockData[] = [
                    'sku' => $sku,
                    'name' => $name,
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
     * @param $name
     * @param $count
     */
    public function addToStock(User $recipient, User $sender, $sku, $name, $count)
    {
        // 1. Megnézzük, hogy van-e már ilyen termékből készlete
        $stockItem = $recipient->stock()->where('sku', $sku)->first() ?? new Stock();
        $oldInventory = $stockItem->inventory_on_hand ?? 0; // Elmentjük, ha volt régi készlete

        $stockItem->user_id = $recipient->id;
        $stockItem->sku = $sku;
        $stockItem->name = $name;
        $stockItem->inventory_on_hand += $count;
        $stockItem->save();

        $history = new StockHistory();
        $history->recipient = $recipient->id;
        $history->sender = $sender->id;
        $history->sku = $sku;
        $history->name = $name;
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
        $history->name = $stockItem->name;
        $history->amount = $newInventory - $oldInventory;
        $history->save();
    }

    /**
     * Visszaadja átalakítva a megrendelt termékből az összetevőket.
     *
     * @param $sku
     * @return bool|mixed
     */
    public function getPartsFromSku($sku)
    {
//        dump(strval($sku));
//        dd($this->skuMap);
        if (!array_key_exists(strval($sku), $this->skuMap)) {
            \Log::error('----- ESÓES BAJ VAN! -------');
            \Log::error(sprintf('-- NEM TALÁLHATÓ SKU: "%s" --', strval($sku)));
            \Mail::raw(sprintf('Baj van, nem található ez az SKU: "%s"', strval($sku)), function(Message $message) {
                $message->subject('Viszonteladó Portál HIBA')->to('dev.mihodaniel@gmail.com');
            });
            return false;
        }
        return $this->skuMap[strval($sku)];
    }
}