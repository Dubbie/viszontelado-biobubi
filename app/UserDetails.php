<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDetails extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function shippingAddress() {
        return $this->hasOne(Address::class, 'id', 'shipping_address_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function billingAddress() {
        return $this->hasOne(Address::class, 'id', 'billing_address_id');
    }
}
