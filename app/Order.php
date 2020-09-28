<?php

namespace App;

use App\Mail\RegularOrderCompleted;
use App\Mail\TrialOrderCompleted;
use App\Subesz\BillingoNewService;
use App\Subesz\BillingoService;
use App\Subesz\ShoprenterService;
use App\Subesz\StockService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function reseller() {
        return $this->hasOne(User::class, 'id', 'reseller_id');
    }

    /**
     * @return array
     */
    public function getReseller() {
        return [
            'resellers' => $this->reseller,
            'correct' => $this->reseller,
        ];
        /*// Megnézzük, hogy a Viszonteladók irányítószámai közt benne van-e
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
        ];*/
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments() {
        return $this->hasMany(OrderComment::class, 'order_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function todos() {
        return $this->hasMany(OrderTodo::class, 'order_id', 'id')->whereHas('User', function(Builder $query) {
            $query->where('user_id', \Auth::id());
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products() {
        return $this->hasMany(OrderProducts::class, 'order_id', 'id');
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
     * @return Collection
     */
    public function getBaseProducts() {
        $orderBaseProducts = new Collection();
        if (count($this->products) > 0) {
            /** @var OrderProducts $orderProduct */
            foreach ($this->products as $orderProduct) {
                // Kiszedjük, a darabjait
                foreach ($orderProduct->product->getSubProducts() as $baseProduct) {
                    // Felszorozzuk annyival, amennyit rendelt
                    $baseProduct['count'] *= $orderProduct->product_qty;
                    $orderBaseProducts->add($baseProduct);
                }
            }
        }

        return $orderBaseProducts;
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
            if (in_array($product->sku, Product::where('trial_product', '=', true)->pluck('sku')->toArray())) {
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
            return null;
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
        if (!$this->isInvoiceSaved()) {
            Log::error(sprintf('Nincs elmentve a megrendeléshez számla... (Helyi megrendelés azonosító: %s)', $this->id));
            return false;
        }

        // Elvileg megvan minden, mehet a levél
        if (!$this->hasTrial()) {
            \Mail::to($this->email)->send(new RegularOrderCompleted($this, $this->invoice_path));
        } else {
            \Mail::to($this->email)->send(new TrialOrderCompleted($this, $this->invoice_path));
        }

        return true;
    }

    protected static function booted()
    {
        // Létrehozásnál nézzünk viszonteladót a megrendeléshez
        static::creating(function (Order $order) {
            /** @var UserZip $uZip */
            $uZip = UserZip::where('zip', $order->shipping_postcode)->first();
            if ($uZip) {
                $order->reseller_id = $uZip->user->id;
            } else {
                $order->reseller_id = env('ADMIN_USER_ID');
            }
        });

        static::created(function (Order $order) {
            Log::info('Helyi megrendelés elmentve, hozzárendelt viszonteladó: ' . User::find($order->reseller_id)->name);
        });

        // Törléskör a termékeket kukázzuk
        static::deleting(function ($order) {
            /** @var Order $order */
            if ($order->products) {
                $baseProducts = $order->getBaseProducts();
                foreach ($baseProducts as $baseProduct) {
                    /** @var Product $product */
                    /** @var User $reseller */
                    /** @var Stock $stockItem */
                    $product = $baseProduct['product'];
                    $stockCount = $baseProduct['count'];
                    $reseller = $order->getReseller()['correct'];
                    $stockItem = $reseller->stock()->where('sku', $product->sku)->first();

                    if ($stockItem && $order->status_text == 'Teljesítve') {
                        $stockItem->inventory_on_hand += $stockCount;
                        $stockItem->save();
                    }
                }
            }
        });
    }
}
