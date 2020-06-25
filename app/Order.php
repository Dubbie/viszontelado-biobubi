<?php

namespace App;

use App\Mail\RegularOrderCompleted;
use App\Mail\TrialOrderCompleted;
use App\Subesz\BillingoService;
use App\Subesz\ShoprenterService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Class Order
 * @package App
 * @mixin Order
 */
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
            $reseller = User::where('email', '=', 'hello@semmiszemet.hu')->first();
            $resellers = [$reseller];
       }

        return [
            'resellers' => $resellers,
            'correct' => $reseller,
        ];
    }

    /**
     * @return array
     */
    public function getShoprenterOrder() {
        /** @var ShoprenterService $ss */
        $ss = resolve('App\Subesz\ShoprenterService');
        return $ss->getOrder($this->inner_resource_id);
    }

    /**
     * @return bool
     */
    public function isInvoiceSaved() {
        return $this->invoice_path !== null;
    }

    /**
     * @return bool
     */
    public function hasTrial() {
        $order = $this->getShoprenterOrder();
        $trial = false;

        foreach ($order['products']->items as $product) {
            if (in_array($product->sku, TrialProduct::all()->pluck('sku')->toArray())) {
                $trial = true;
                break;
            }
        }

        return $trial;
    }

    /**
     * @return bool
     */
    public function sendInvoice() {
        /** @var BillingoService $bs */
        $bs = resolve('App\Subesz\BillingoService');

        if (!$this->isInvoiceSaved()) {
            Log::error('Nincs elmentve a megrendeléshez számla... Számla azonosító keresése...');
            // Megnézzük, hogy van-e elmentve azonosító a számlához
            if ($this->invoice_id) {
                Log::info('Számla azonosító megtalálva! Elmentés megkezdése...');
                $reseller = $this->getReseller()['correct'];
                if (!$bs->saveInvoice($reseller, $this->invoice_id, $this->id)) {
                    return false;
                }
            } else {
                Log::error('Nincs elmentve számla azonosító sem, a levél kiküldése sikertelen!');
                return false;
            }
        }

        // Elvileg megvan minden, mehet a levél
        if (!$this->hasTrial()) {
            \Mail::to('dev.mihodaniel@gmail.com')->send(new RegularOrderCompleted($this, $this->invoice_path));
        } else {
            \Mail::to('dev.mihodaniel@gmail.com')->send(new TrialOrderCompleted($this, $this->invoice_path));
        }

        return true;
    }
}
