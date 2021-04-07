<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class RegionZip extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function region(): HasOne {
        return $this->hasOne(Region::class, 'id', 'region_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function reseller(): HasOneThrough {
        return $this->hasOneThrough(User::class, Region::class, 'id', 'id', 'region_id', 'user_id');
    }
}
