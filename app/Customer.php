<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    /**
     * Visszaadja az ügyfél nevét formázva.
     *
     * @return string
     */
    public function getFormattedName(): string
    {
        return sprintf('%s %s', mb_convert_case($this->firstname, MB_CASE_TITLE),
            mb_convert_case($this->lastname, MB_CASE_TITLE));
    }

    /**
     * Visszaadja az ügyfél címét formázva.
     *
     * @param  bool  $convert
     * @return string
     */
    public function getFormattedAddress(bool $convert = true): string
    {
        if ($convert) {
            return sprintf('%s %s, %s', mb_convert_case($this->postcode, MB_CASE_TITLE),
                mb_convert_case($this->city, MB_CASE_TITLE),
                mb_convert_case($this->address, MB_CASE_TITLE));
        } else {
            return sprintf('%s %s, %s', $this->postcode,
                $this->city,
                $this->address);
        }
    }

    /**
     * Visszaadja, hogy mikor rendelt utoljára szépen formázva.
     *
     * @return string
     */
    public function getLastOrderTimeAgo()
    {
        $lastDate = Carbon::createFromFormat('Y.m.d', $this->getLastOrderDate());
        return $lastDate->shortRelativeToNowDiffForHumans(Carbon::now());
    }

    /**
     * @return null
     */
    public function getLastOrderDate()
    {
        if ($this->orders()->count() > 0) {
            /** @var Order $order */
            $order = $this->orders()->orderByDesc('created_at')->first();
            return $order->created_at->format('Y.m.d');
        }

        return null;
    }

    /**
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'email', 'email');
    }
}
