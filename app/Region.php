<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;

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
	public function zips(): HasMany
	{
		return $this->hasMany(RegionZip::class, 'region_id', 'id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function user(): HasOne
	{
		return $this->hasOne(User::class, 'id', 'user_id');
	}

	/**
	 * @return array|false|string
	 */
	public function encodeZips()
	{
		return resolve('App\Subesz\RegionService')->encodeRegionZipsByRegion($this->id);
	}

	public function setBiobubiDelivery($toggle)
	{
		Log::info("BioBubi futár frissítése: ");
		Log::info(" - Régió: " . $this->name);
		Log::info(" - Szállít: " . ($toggle ? "Igen" : "Nem"));

		$this->biobubi_delivery = $toggle;
		$success = $this->save();

		if ($success) {
			Log::info("Sikeres frissítés");
		} else {
			Log::error("Hiba történt a BioBubi futár frissítésekor.");
		}

		return $success;
	}
}
