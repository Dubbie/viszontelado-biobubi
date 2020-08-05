<?php

namespace App;

use App\Mail\RegularOrderCompleted;
use App\Mail\TrialOrderCompleted;
use App\Subesz\BillingoNewService;
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
    protected $fillable = [
        'inner_id',
        'inner_resource_id',
        'total',
        'total_gross',
        'tax_price',
        'firstname',
        'lastname',
        'email',
        'status_text',
        'status_color',
        'shipping_method_name',
        'payment_method_name',
        'shipping_postcode',
        'shipping_city',
        'shipping_address',
        'created_at',
        'updated_at',
    ];

    /**
     * @return string
     */
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
     * @return null|\Swagger\Client\Model\Document
     */
    public function getDraftInvoice() {
        /** @var BillingoNewService $bs */
        $bs = resolve('App\Subesz\BillingoNewService');
        $reseller = $this->getReseller()['correct'];

        return $bs->getInvoice($this->draft_invoice_id, $reseller);
    }

    /**
     * @return null|\Swagger\Client\Model\Document
     */
    public function createRealInvoice() {
        if (!$this->draft_invoice_id) {
            Log::error(sprintf('Hiba történt az átalakításkor, nincs kitöltve piszkozat számla azonosító! (Helyi megrendelési azonosító: %s)', $this->id));
        }

        /** @var BillingoNewService $bs */
        $bs = resolve('App\Subesz\BillingoNewService');
        $reseller = $this->getReseller()['correct'];

        return $bs->getRealInvoiceFromDraft($this->draft_invoice_id, $reseller);
    }

    /**
     * @return bool
     */
    public function sendInvoice() {
        /** @var BillingoNewService $bs */
        $bs = resolve('App\Subesz\BillingoNewService');

        if (!$this->isInvoiceSaved()) {
            Log::error('Nincs elmentve a megrendeléshez számla... Számla azonosító keresése...');
            // Megnézzük, hogy van-e elmentve azonosító a számlához
            if ($this->invoice_id) {
                Log::info('Számla azonosító megtalálva! Elmentés megkezdése...');
                $reseller = $this->getReseller()['correct'];
                if (!$bs->saveInvoice($this->invoice_id, $this->id, $reseller)) {
                    return false;
                }
            } else {
                Log::error('Nincs elmentve számla azonosító sem, a levél kiküldése sikertelen!');
                return false;
            }
        }

        // Elvileg megvan minden, mehet a levél
//        if (!$this->hasTrial()) {
//            \Mail::to($this->email)->send(new RegularOrderCompleted($this, $this->invoice_path));
//        } else {
//            \Mail::to($this->email)->send(new TrialOrderCompleted($this, $this->invoice_path));
//        }
        if (!$this->hasTrial()) {
            \Mail::to('dev.mihodaniel@gmail.com')->send(new RegularOrderCompleted($this, $this->invoice_path));
        } else {
            \Mail::to('dev.mihodaniel@gmail.com')->send(new TrialOrderCompleted($this, $this->invoice_path));
        }

        return true;
    }
}
