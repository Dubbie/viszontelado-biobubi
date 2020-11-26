<?php

namespace App\Subesz;


use App\Address;
use App\User;

class AddressService
{
    /**
     * @param $zip
     * @param $city
     * @param $addr1
     * @param $addr2
     * @return Address|null
     */
    public function storeAddress($zip, $city, $addr1, $addr2) {
        if (strlen($zip) == 0 || strlen($city) == 0 || strlen($addr1) == 0) {
           \Log::info('Nincs kitöltve minden szükséges mező, ezért nem tudja a rendszer létrehzni a címet.');

           return null;
        }

        $addr = new Address();
        $addr->zip = $zip;
        $addr->city = $city;
        $addr->address1 = $addr1;
        $addr->address2 = $addr2;
        $addr->save();

        \Log::info(sprintf('Új cím elmentve! (azonosító: %s)', $addr->id));
        \Log::info(sprintf('- Irányítószám: %s', $addr->zip));
        \Log::info(sprintf('- Város: %s', $addr->zip));
        \Log::info(sprintf('- Cím: %s', $addr->address1));
        \Log::info(sprintf('- Cím kieg: %s', $addr->address2));

        return $addr;
    }
}