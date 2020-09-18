<?php

namespace App;

use App\Subesz\StockService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use PhpParser\Builder\Declaration;

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

        $orderedProducts = OrderProducts::whereHas('order', function (Builder $query) use ($reseller) {
            $query->where([
                ['orders.reseller_id', $reseller->id],
                ['orders.status_text', 'Függőben lévő'],
            ]);
        })->get();
        foreach ($orderedProducts as $op) {
            // 1. eset, EZ MONEY
            if ($op->product_sku == $this->sku) {
                $booked += ($op->product_qty);
            } else {
                // 2. eset, megnézzük, hogy ha ez csomag termék és tartalmazza ezt az SKU-t akkor mennyivel növeljük
                /** @var BundleProduct[] $bps */
                $bps = BundleProduct::where('bundle_sku', $op->product_sku)->get();
                foreach ($bps as $bp) {
                    if ($bp->product_sku == $this->sku) {
                        $booked += ($op->product_qty * $bp->product_qty);
                    }
                }
            }
        }

        return $booked;
    }

    public function getSoldCount() {
        /** @var User $reseller */
        /** @var Order $order */
        $resellerId = $this->user_id;
        $sold = 0;

        $orderedProducts = OrderProducts::whereHas('order', function (Builder $query) use ($resellerId) {
            $query->where([
                ['orders.reseller_id', $resellerId],
                ['orders.status_text', 'Teljesítve'],
            ]);
        })->get();
        foreach ($orderedProducts as $op) {
            // 1. eset, EZ MONEY
            if ($op->product_sku == $this->sku) {
                $sold += $op->product_qty;
            } else {
                // 2. eset, megnézzük, hogy ha ez csomag termék és tartalmazza ezt az SKU-t akkor mennyivel növeljük
                /** @var BundleProduct[] $bps */
                $bps = BundleProduct::where('bundle_sku', $op->product_sku)->get();
                foreach ($bps as $bp) {
                    if ($bp->product_sku == $this->sku) {
                        $sold += ($op->product_qty * $bp->product_qty);
                    }
                }
            }
        }

        return $sold;
    }
}
