<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BenjiMoney extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @param $value
     * @return Carbon
     */
    public function getGivenAtAttribute($value) {
        return Carbon::parse($value);
    }
}
