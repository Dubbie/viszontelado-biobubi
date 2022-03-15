<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MoneyTransferOrder extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function order(): HasOne {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }
}
