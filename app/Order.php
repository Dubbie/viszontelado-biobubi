<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function getFormattedAddress() {
        $out = '';

        if ($this->shipping_postcode && $this->shipping_city && $this->shipping_address) {
            $out = sprintf('%s %s, %s', $this->shipping_postcode, $this->shipping_city, $this->shipping_address);
        }

        return $out;
    }
}
