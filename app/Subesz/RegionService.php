<?php

namespace App\Subesz;

use App\Region;

class RegionService
{
    /**
     * Visszaadja az irányítószámokat olyan formában, amit elfogad az input mezőn lévő JS plugin.
     *
     * @param $regionId
     * @return array|false|string
     */
    public function encodeRegionZipsByRegion($regionId) {
        $region   = Region::find($regionId);
        $response = [
            'success' => false,
            'message' => 'Régió átalakítása JSON irányítószámokra',
        ];

        if (! $region) {
            $response['message'] = 'Nem található ilyen azonosítójú régió (Azonosító: '.$regionId.')';

            return $response;
        }

        $return = [];
        foreach ($region->zips as $rZip) {
            $return[] = [
                'value' => $rZip->zip,
            ];
        }

        return json_encode($return);
    }
}