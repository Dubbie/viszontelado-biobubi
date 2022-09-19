<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class StockMovement
 *
 * @package App
 * @mixin StockMovement
 */
class StockMovement extends Model
{
    protected $table = 'stock_movement';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function product(): HasOne {
        return $this->hasOne(Product::class, 'sku', 'product_sku');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function reseller(): HasOne {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
