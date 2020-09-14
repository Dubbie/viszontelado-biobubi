<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $primaryKey = 'sku';
    public $incrementing = false;

    public function subProducts() {
        return $this->hasMany(BundleProduct::class, 'bundle_sku', 'sku');
    }
}
