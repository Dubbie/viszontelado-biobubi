<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Region
 *
 * @package App
 * @mixin \App\Region
 */
class Region extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function zips(): HasMany {
        return $this->hasMany(RegionZip::class, 'region_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user(): HasOne {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @return array|false|string
     */
    public function encodeZips() {
        return resolve('App\Subesz\RegionService')->encodeRegionZipsByRegion($this->id);
    }
}
