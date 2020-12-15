<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Address
 * @package App
 * @mixin Address
 */
class Address extends Model
{
    /**
     * @return string
     */
    public function getFormattedAddress()
    {
        $o = sprintf('%s %s, %s', $this->zip, $this->city, $this->address1);
        if ($this->address2) {
            $o .= ' ' . $this->address2;
        }
        return $o;
    }
}
