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

    /**
     * @return array
     */
    public function getReseller() {
        // Megnézzük, hogy a Viszonteladók irányítószámai közt benne van-e
        $userZips = UserZip::where('zip', $this->shipping_postcode)->get();
        $resellers = [];
        $reseller = null;

        if (count($userZips) > 0) {
            foreach ($userZips as $userZip) {
               $resellers[] = $userZip->user;
           }

           // Alapértelmezetten berakjuk az elsőt, utána ha admint talál akkor felülirja
           $reseller = $resellers[0];
           foreach ($resellers as $_reseller) {
               if ($_reseller->admin) {
                   $reseller = $_reseller;
                   break;
               }
           }
       } else {
            // Találjuk meg az admint akinek van irányítószáma
            $resellers = User::where('admin', true)->get();

            foreach ($resellers as $_reseller) {
                if (count($_reseller->zips) > 0) {
                    $reseller = $_reseller;
                    break;
                }
            }
       }

        return [
            'resellers' => $resellers,
            'correct' => $reseller,
        ];
    }
}
