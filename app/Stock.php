<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Stock
 *
 * @package App
 * @mixin Stock
 */
class Stock extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function product(): HasOne {
        return $this->hasOne(Product::class, 'sku', 'sku');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function reseller(): HasOne {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @return int
     */
    public function getBookedCount() {
        /** @var User $reseller */
        /** @var Order $order */
        $reseller = $this->reseller;

        return OrderProducts::whereHas('order', function (Builder $query) use ($reseller) {
            $query->where([
                ['orders.reseller_id', $reseller->id],
                ['orders.status_text', 'Függőben lévő'],
            ]);
        })->where('product_sku', '=', $this->sku)->sum('product_qty');
    }

    public function getSoldCount() {
        /** @var User $reseller */
        /** @var Order $order */
        $reseller = $this->reseller;

        return OrderProducts::whereHas('order', function (Builder $query) use ($reseller) {
            $query->where([
                ['orders.reseller_id', $reseller->id],
                ['orders.status_text', 'Függőben lévő'],
            ]);
        })->where('product_sku', '=', $this->sku)->sum('product_qty');
    }
}
