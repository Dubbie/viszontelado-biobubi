<?php

namespace App\Subesz;


use App\Stock;
use App\StockHistory;
use App\User;

class StockService
{
    public function addToStock(User $recipient, User $sender, $sku, $name, $count) {
        $stockItem = new Stock();
        $stockItem->user_id = $recipient->id;
        $stockItem->sku = $sku;
        $stockItem->name = $name;
        $stockItem->inventory_on_hand = $count;
        $stockItem->save();

        $history = new StockHistory();
        $history->recipient = $recipient->id;
        $history->sender = $sender->id;
        $history->sku = $sku;
        $history->name = $name;
        $history->amount = $count;
        $history->save();
    }
}