<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Stock
 * @package App
 * @mixin Stock
 */
class Stock extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function product() {
        return $this->hasOne(Product::class, 'sku', 'sku');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function reseller() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @return int
     */
    public function getBookedCount() {
        /** @var User $reseller */
        /** @var Order $order */
        $reseller = $this->reseller;
        $booked = 0;

        foreach ($reseller->getOrdersWithProducts()->where('status_text','==',  'Függőben lévő') as $order) {
            $found = $order->getBaseProducts()->where('product.sku', $this->sku)->first();
            if($found) {
                $booked += $found['count'];
            }
        }

        return $booked;
    }

    public function getSoldCount() {
        /** @var User $reseller */
        /** @var Order $order */
        $reseller = $this->reseller;
        $sold = 0;

        foreach ($reseller->getOrdersWithProducts()->where('status_text','==',  'Teljesítve') as $order) {
            $found = $order->getBaseProducts()->where('product.sku', $this->sku)->first();
            if($found) {
                $sold += $found['count'];
            }
        }

        return $sold;
    }
}
