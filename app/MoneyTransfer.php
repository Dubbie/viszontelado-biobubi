<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class MoneyTransfer
 *
 * @package App
 * @mixin \App\MoneyTransfer
 */
class MoneyTransfer extends Model
{
    /**
     * @param $value
     * @return \Carbon\Carbon|null
     */
    public function getCompletedAtAttribute($value): ?Carbon {
        return $value ? Carbon::parse($value) : $value;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function reseller(): HasOne {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transferOrders(): HasMany {
        return $this->hasMany(MoneyTransferOrder::class, 'transfer_id', 'id');
    }

    /**
     * @return string
     */
    public function getStatusText(): string {
        return $this->isCompleted() ? 'Elutalva' : 'Utalás alatt';
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool {
        return $this->completed_at !== null;
    }

    /**
     * @return string
     */
    public function getTextColorClass(): string {
        return $this->isCompleted() ? 'text-success-pastel' : 'text-info-pastel';
    }

    /**
     * @return string
     */
    public function getId(): string {
        return '#BBT-'.str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Visszaadja a jutalék összegét.
     *
     * @return false|float|int
     */
    public function getCommissionFee() {
        $grossSum = -1;
        foreach ($this->transferOrders as $mto) {
            if ($mto->reduced_value) {
                $grossSum += $mto->order->total_gross;
            }
        }

        if ($grossSum != -1) {
            return $grossSum - $this->amount;
        } else {
            return false;
        }
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed                                  $containsQuery
     * @return mixed
     */
    public function scopeContains(Builder $query, $containsQuery) {
        return $query->where('id', 'like', '%'.$containsQuery.'%');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeCompleted($query) {
        return $query->where('completed_at', '!=', null);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeIncomplete($query) {
        return $query->where('completed_at', null);
    }
}
