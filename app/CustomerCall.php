<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class CustomerCall
 *
 * @package App
 * @mixin \App\CustomerCall
 */
class CustomerCall extends Model
{
    /**
     * @param $value
     * @return \Carbon\Carbon
     */
    public function getDueDateAttribute($value) {
        return Carbon::parse($value);
    }

    /**
     * @param $value
     * @return \Carbon\Carbon
     */
    public function getCalledAtAttribute($value): ?Carbon {
        if ($value) {
            return Carbon::parse($value);
        }

        return null;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function customer(): HasOne {
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function reseller(): HasOne {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @return string
     */
    public function getRemainingTime(): string {
        return $this->due_date->shortRelativeToNowDiffForHumans($this->created_at);
    }

    /**
     * @return bool
     */
    public function isOverdue(): bool {
        return $this->due_date < Carbon::now() && ! $this->called_at;
    }
}
