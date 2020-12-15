<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CentralStock extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function product() {
        return $this->hasOne(Product::class, 'sku', 'product_sku');
    }
}
