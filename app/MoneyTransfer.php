<?php

namespace App;

use Carbon\Carbon;
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
    public function getCompletedAtAttribute($value) {
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
     * @return bool
     */
    public function isCompleted() {
        return $this->completed_at !== null;
    }

    /**
     * @return string
     */
    public function getStatusText() {
        return $this->isCompleted() ? 'Elutalva' : 'Utalás alatt';
    }

    /**
     * @return string
     */
    public function getTextColorClass() {
        return $this->isCompleted() ? 'text-success-pastel' : 'text-danger-pastel';
    }

    /**
     * @return string
     */
    public function getId() {
        return '#BBT-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }
}
