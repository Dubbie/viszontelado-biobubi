<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BundleProduct extends Model
{
    public function product() {
        return $this->hasOne(Product::class, 'sku', 'product_sku');
    }
}
