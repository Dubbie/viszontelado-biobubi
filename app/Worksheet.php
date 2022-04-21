<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Worksheet
 *
 * @package App
 * @mixin Worksheet
 */
class Worksheet extends Model
{
    /**
     * @return BelongsTo
     */
    public function localOrder(): BelongsTo {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function user(): HasOne {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
