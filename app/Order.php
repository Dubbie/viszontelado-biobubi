<?php

namespace App;

use App\Mail\OrderAdvanceInvoiceSent;
use App\Mail\RegularOrderCompleted;
use App\Mail\TrialOrderCompleted;
use App\Subesz\BillingoNewService;
use App\Subesz\ShoprenterService;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Mail;

/**
 * Class Order
 *
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
        'phone',
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

    protected static function booted() {
        // Létrehozásnál nézzünk viszonteladót a megrendeléshez
        static::creating(function (Order $order) {
            /** @var \App\RegionZip $rZip */
            $rZip = RegionZip::where('zip', $order->shipping_postcode)->first();
            if ($rZip) {
                $order->reseller_id = $rZip->reseller->id;
            } else {
                $order->reseller_id = env('ADMIN_USER_ID');
            }
        });

        static::created(function (Order $order) {
            Log::info('Helyi megrendelés elmentve, hozzárendelt viszonteladó: '.User::find($order->reseller_id)->name);
        });

        // Törléskör a termékeket kukázzuk
        static::deleting(function ($order) {
            /** @var Order $order */
            if ($order->products) {
                $baseProducts = $order->getBaseProducts();
                foreach ($baseProducts as $baseProduct) {
                    /** @var Product $product */ /** @var User $reseller */
                    /** @var Stock $stockItem */
                    $product    = $baseProduct['product'];
                    $stockCount = $baseProduct['count'];
                    $reseller   = $order->getReseller()['correct'];
                    $stockItem  = $reseller->stock()->where('sku', $product->sku)->first();

                    if ($stockItem && $order->status_text == 'Teljesítve') {
                        $stockItem->inventory_on_hand += $stockCount;
                        $stockItem->save();
                    }
                }
            }
        });
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
     * @return array
     */
    public function getReseller() {
        return [
            'resellers' => $this->reseller,
            'correct'   => $this->reseller,
        ];
    }

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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments() {
        return $this->hasMany(OrderComment::class, 'order_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function todos() {
        return $this->hasMany(OrderTodo::class, 'order_id', 'id')->whereHas('User', function (Builder $query) {
            $query->where('user_id', Auth::id());
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function delivery(): HasOne {
        return $this->hasOne(Delivery::class, 'order_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products() {
        return $this->hasMany(OrderProducts::class, 'order_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function income() {
        return $this->hasOne(Income::class, 'id', 'income_id');
    }

    /**
     * @return null|\Swagger\Client\Model\Document
     */
    public function getDraftInvoice() {
        /** @var BillingoNewService $bs */
        $bs       = resolve('App\Subesz\BillingoNewService');
        $reseller = $this->getReseller()['correct'];

        return $bs->getInvoice($this->draft_invoice_id, $reseller);
    }

    /**
     * @return bool
     */
    public function isPending(): bool {
        return in_array($this->status_text, [
            'Függőben lévő',
            'BK. Függőben lévő',
        ]);
    }

    /**
     * @return bool
     */
    public function isOverdue(): bool {
        return ! $this->isCompleted() && (Carbon::now() > $this->getDeadline());
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool {
        return resolve('App\Subesz\StatusService')->isCompleted($this->id);
    }

    /**
     * @return Carbon
     */
    public function getDeadline(): Carbon {
        /** @var Carbon $deadline */
        /** @var Carbon $ordered_at */
        $ordered_at = $this->created_at;
        $deadline   = $ordered_at->clone()->nextWeekday();

        return $deadline;
    }

    /**
     * @return bool
     */
    public function isBankkcard(): bool {
        return $this->payment_method_name == 'Online bankkártyás fizetés';
    }

    /**
     * Visszaadja, hogy a megrendelés a bejelentkezett felhasználó munkalapján szerepel-e.
     *
     * @return bool
     */
    public function onWorksheet(): bool {
        return $this->getWorksheetEntry() ? true : false;
    }

    /**
     * Visszaadja a munkalap elemet a megrendeléshez a jelenleg bejelentkezett felhasználóhoz.
     *
     * @return \App\Worksheet|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getWorksheetEntry() {
        return Worksheet::where([
            ['user_id', '=', Auth::id()],
            ['order_id', '=', $this->id],
        ])->first();
    }

    /**
     * @return float|int
     */
    public function getProgress() {
        $start   = $this->created_at->getTimestamp();
        $end     = $this->getDeadline()->getTimestamp() - $start;
        $now     = time();
        $elapsed = $now - $start;

        if ($elapsed > $end) {
            return 1;
        }
        if ($now < $start) {
            return 0;
        }

        return ($elapsed / $end) * 100;
    }

    /**
     * @return array
     */
    public function createAdvanceInvoice(): array {
        $bs       = resolve('App\Subesz\BillingoNewService');
        $response = [
            'success' => false,
            'message' => 'Előlegszámla létrehozásának inicalizálása',
        ];

        if (! $bs->isBillingoConnected($this->reseller)) {
            Log::info('A felhasználónak nincs billingo összekötése, ezért nem készül előlegszámla.');
        } else {
            // Ha még nem jött létre előlegszámla, sem pedig éles számla, akkor létrehozzuk
            // - Éles számlát azért ellenőrizzük, mert jelenleg előleg számla hiányában éles számlát küldene a rendszer
            if ($this->draft_invoice_id && (! $this->advance_invoice_path && ! $this->advance_invoice_id) && (! $this->invoice_path && ! $this->invoice_id)) {
                // 1. Létrehozzuk az előleg számlát, ha sikerül
                $advanceInvoice = $this->generateAdvanceInvoice();
                if (! $advanceInvoice) {
                    Log::error(sprintf('Nem sikerült létrehozni az előlegszámlát. (Piszkozat: %s, Megr. Azonosító: %s)', $this->draft_invoice_id, $this->id));
                    $response['message'] = 'Hiba történt a piszkozat számla átalakításakor';

                    return $response;
                } else {
                    // Jók vagyunk
                    $this->advance_invoice_id = $advanceInvoice->getId();
                    $this->save();
                    $this->refresh();
                    $path = $bs->downloadInvoice($advanceInvoice->getId(), $this, $this->reseller);
                    if (! $path) {
                        Log::error('Hiba történt az előlegszámla letöltésekor');
                        $response['message'] = 'Hiba történt az előlegszámla letöltésekor';

                        return $response;
                    }

                    // Elmentjük a számlát helyileg
                    $this->advance_invoice_path = $path;
                    $this->save();
                    $this->sendAdvanceInvoice();
                    $response['success'] = true;
                    $response['message'] = 'Előlegszámla sikeresen létrehozva és elküldve az ügyfélnek';
                }
            } else {
                if ($this->draft_invoice_id && $this->advance_invoice_id && $this->advance_invoice_path) {
                    Log::info(sprintf('A megrendeléshez már létrejött előlegszámla ezért nem hozunk létre újabbat. (Megr. Azonosító: %s)', $this->id));
                    $response['success'] = true;
                    $response['message'] = sprintf('A megrendeléshez már létrejött előlegszámla ezért nem hozunk létre újabbat. (Megr. Azonosító: %s)', $this->id);
                } else {
                    Log::error('Nincs se régi se új számla azonosító, nem lehet létrehozni számlát automatikusan (Régi megrendelés)');
                    $response['success'] = true;
                    $response['message'] = 'Nincs se régi se új számla azonosító, nem lehet létrehozni számlát automatikusan (Régi megrendelés)';
                }
            }
        }

        return $response;
    }

    /**
     * @return null|\Swagger\Client\Model\Document
     */
    public function generateAdvanceInvoice(): ?\Swagger\Client\Model\Document {
        if (! $this->draft_invoice_id) {
            Log::error(sprintf('Hiba történt az átalakításkor, nincs kitöltve piszkozat számla azonosító! (Helyi megrendelési azonosító: %s)', $this->id));

            return null;
        }

        /** @var BillingoNewService $bs */
        $bs       = resolve('App\Subesz\BillingoNewService');
        $reseller = $this->getReseller()['correct'];

        return $bs->getAdvanceInvoiceFromDraft($this->draft_invoice_id, $reseller, $this);
    }

    /**
     * @return bool
     */
    public function sendAdvanceInvoice(): bool {
        if (! $this->isAdvanceInvoiceSaved()) {
            Log::error(sprintf('Nincs elmentve a megrendeléshez előlegszámla... (Helyi megrendelés azonosító: %s)', $this->id));

            return false;
        }

        // Elvileg megvan minden, mehet a levél
        Mail::to($this->email)->send(new OrderAdvanceInvoiceSent($this, $this->advance_invoice_path));

        return true;
    }

    /**
     * @return bool
     */
    public function isAdvanceInvoiceSaved(): bool {
        return $this->advance_invoice_path !== null;
    }

    /**
     * @return array
     */
    public function createInvoice(): array {
        $bs       = resolve('App\Subesz\BillingoNewService');
        $response = [
            'success' => false,
            'message' => 'Számla létrehozásának inicalizálása',
        ];

        if (! $bs->isBillingoConnected($this->reseller)) {
            Log::info('A felhasználónak nincs billingo összekötése, ezért nem készül számla.');
        } else {
            // Csak az új típusú számlázást támogatjuk mostantól, és csak akkor hozzuk létre, ha nincs még számla
            if ($this->draft_invoice_id && (! $this->invoice_path && ! $this->invoice_id)) {
                // 1. Létrehozzuk az éles számlát, ha sikerül
                $realInvoice = $this->createRealInvoice();
                if (! $realInvoice) {
                    Log::error(sprintf('Nem sikerült létrehozni valódi számlát. (Piszkozat: %s, Megr. Azonosító: %s)', $this->draft_invoice_id, $this->id));
                    $response['message'] = 'Hiba történt a piszkozat számla átalakításakor';

                    return $response;
                } else {
                    // Jók vagyunk
                    $this->invoice_id = $realInvoice->getId();
                    $this->save();
                    $this->refresh();
                    $path = $bs->downloadInvoice($realInvoice->getId(), $this, $this->reseller);
                    if (! $path) {
                        Log::error('Hiba történt a számla letöltésekor');
                        $response['message'] = 'Hiba történt a számla letöltésekor';

                        return $response;
                    }

                    // Elmentjük a számlát helyileg
                    $this->invoice_path = $path;
                    $this->save();
                    $this->sendInvoice();
                    $response['success'] = true;
                    $response['message'] = 'Számla sikeresen létrehozva és elküldve az ügyfélnek';
                }
            } else {
                if ($this->draft_invoice_id && $this->invoice_id && $this->invoice_path) {
                    Log::info(sprintf('A megrendeléshez már létrejött számla ezért nem hozunk létre újabbat. (Megr. Azonosító: %s)', $this->id));
                    $response['success'] = true;
                    $response['message'] = sprintf('A megrendeléshez már létrejött számla ezért nem hozunk létre újabbat. (Megr. Azonosító: %s)', $this->id);
                } else {
                    Log::error('Nincs se régi se új számla azonosító, nem lehet létrehozni számlát automatikusan (Régi megrendelés)');
                    $response['success'] = true;
                    $response['message'] = 'Nincs se régi se új számla azonosító, nem lehet létrehozni számlát automatikusan (Régi megrendelés)';
                }
            }
        }

        return $response;
    }

    /**
     * @return null|\Swagger\Client\Model\Document
     */
    public function createRealInvoice(): ?\Swagger\Client\Model\Document {
        if (! $this->draft_invoice_id) {
            Log::error(sprintf('Hiba történt az átalakításkor, nincs kitöltve piszkozat számla azonosító! (Helyi megrendelési azonosító: %s)', $this->id));

            return null;
        }

        if ($this->payment_method_name == 'Online bankkártyás fizetés' && ! $this->advance_invoice_id) {
            Log::error(sprintf('Hiba történt az átalakításkor, nincs elmentve előlegszámla azonosító! (Helyi megrendelési azonosító: %s)', $this->id));

            Log::info('Erőltetett előlegszámla legyártás kéne ide');

            return null;
        }

        /** @var BillingoNewService $bs */
        $bs       = resolve('App\Subesz\BillingoNewService');
        $reseller = $this->getReseller()['correct'];

        return $bs->getRealInvoiceFromDraft($this->draft_invoice_id, $reseller, $this, true);
    }

    /**
     * @return bool
     */
    public function sendInvoice(): bool {
        if (! $this->isInvoiceSaved()) {
            Log::error(sprintf('Nincs elmentve a megrendeléshez számla... (Helyi megrendelés azonosító: %s)', $this->id));

            return false;
        }

        // Elvileg megvan minden, mehet a levél
        if (! $this->hasTrial()) {
            Mail::to($this->email)->send(new RegularOrderCompleted($this, $this->invoice_path));
        } else {
            Mail::to($this->email)->send(new TrialOrderCompleted($this, $this->invoice_path));
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isInvoiceSaved(): bool {
        return $this->invoice_path !== null;
    }

    /**
     * @return bool
     */
    public function hasTrial(): bool {
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
     * @return array
     */
    public function getShoprenterOrder() {
        /** @var ShoprenterService $ss */
        $ss = resolve('App\Subesz\ShoprenterService');

        return $ss->getOrder($this->inner_resource_id);
    }

    /**
     * @param  null  $date
     * @return bool
     */
    public function updateIncome($date = null): bool {
        // Ha nincs teljesítve akkor nincs bevételünk...
        if (! $this->isCompleted()) {
            return true;
        }

        $income              = $this->income ?? new Income();
        $income->gross_value = $this->total_gross;
        $income->name        = 'Megrendelés';
        $income->user_id     = $this->reseller_id;
        $income->comment     = sprintf('#%s megrendelésszám (%s %s)', $this->inner_id, $this->firstname, $this->lastname);
        $income->tax_value   = $this->total_gross - ($this->total_gross / 1.27);
        $income->date        = $date ? $date : $this->created_at;
        $success             = $income->save();

        Log::info(sprintf('A #%s megrendelésszámhoz tartozó bevétel elmentve. (%s Ft)', $this->inner_id, $this->total_gross));

        return $success;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDelivered(Builder $query): Builder {
        return $query->has('delivery');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending(Builder $query): Builder {
        return $query->doesntHave('delivery');
    }
}
