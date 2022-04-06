<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function reseller(): HasOne {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments(): HasMany {
        return $this->hasMany(CustomerComment::class, 'customer_id', 'id');
    }

    /**
     * Visszaadja az ügyfél nevét formázva.
     *
     * @return string
     */
    public function getFormattedName(): string {
        return sprintf('%s %s', mb_convert_case($this->firstname, MB_CASE_TITLE), mb_convert_case($this->lastname, MB_CASE_TITLE));
    }

    /**
     * Visszaadja az ügyfél címét formázva.
     *
     * @param  bool  $convert
     * @return string
     */
    public function getFormattedAddress(bool $convert = true): string {
        if ($convert) {
            return sprintf('%s %s, %s', mb_convert_case($this->postcode, MB_CASE_TITLE), mb_convert_case($this->city, MB_CASE_TITLE), mb_convert_case($this->address, MB_CASE_TITLE));
        } else {
            return sprintf('%s %s, %s', $this->postcode, $this->city, $this->address);
        }
    }

    /**
     * Visszaadja, hogy mikor rendelt utoljára szépen formázva.
     *
     * @return string
     */
    public function getLastOrderTimeAgo() {
        $lastDate = $this->getLastOrderDate();

        return $lastDate->shortRelativeToNowDiffForHumans(Carbon::now());
    }

    /**
     * @return Carbon|null
     */
    public function getLastOrderDate(): ?Carbon {
        if ($this->orders()->count() > 0) {
            /** @var Order $order */
            $order = $this->orders()->orderByDesc('created_at')->first();

            return $order->created_at;
        }

        return null;
    }

    /**
     * @return HasMany
     */
    public function orders(): HasMany {
        return $this->hasMany(Order::class, 'email', 'email');
    }
}
