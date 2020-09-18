<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class Product
 * @package App
 * @mixin Product
 */
class Product extends Model
{
    protected $primaryKey = 'sku';
    public $incrementing = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subProducts() {
        return $this->hasMany(BundleProduct::class, 'bundle_sku', 'sku');
    }

    /**
     * @return Collection
     */
    public function getSubProducts() {
        $subProducts = new Collection();

        if ($this->subProducts()->count() > 0) {
            foreach ($this->subProducts as $subProduct) {
                $subProducts->add([
                    'product' => $subProduct->product,
                    'count' => $subProduct->product_qty
                ]);
            }
        } else {
            $subProducts->add([
                'product' => $this,
                'count' => 1,
            ]);
        }

        return $subProducts;
    }

    /**
     * @return \Illuminate\Support\Collection|string
     */
    public function getStockSkuList() {
        return $this->subProducts ? $this->subProducts()->pluck('product_sku') : $this->sku;
    }
}
