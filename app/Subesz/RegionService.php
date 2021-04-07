<?php

namespace App\Subesz;

use App\Region;
use App\RegionZip;
use App\User;
use Log;

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

    /**
     * Visszaadja az irányítószámokat olyan formában, amit elfogad az input mezőn lévő JS plugin.
     *
     * @param  string  $regionZips
     * @return array|false|string
     */
    public function decodeRegionZipsByJSON(string $regionZips) {
        return array_column(json_decode($regionZips, true), 'value');
    }

    /**
     * Legenerálja a régiókat a viszonteladókhoz.
     */
    public function generateRegionsByResellers() {
        Log::info('Régiók generálása a viszonteladók régi irányítószámai alapján');

        $users = User::whereHas('zips')->get();
        foreach ($users as $user) {
            $region          = new Region();
            $region->name    = $user->name.' régió';
            $region->user_id = $user->id;
            $region->save();

            foreach ($user->zips as $userZip) {
                $rz            = new RegionZip();
                $rz->region_id = $region->id;
                $rz->zip       = $userZip->zip;
                $rz->save();
            }

            Log::info(sprintf('Új régió létrehozva: %s (%s irányítószám)', $region->name, $region->zips()->count()));
        }

        if (Region::count() == count($users)) {
            Log::info('Az összes viszonteladóhoz létrejöttek a régiók');
        } else {
            Log::error('Nem jött létre minden viszonteladóhoz a régiója');
        }
    }
}