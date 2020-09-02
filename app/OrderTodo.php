<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderTodo
 * @package App
 * @mixin OrderTodo
 */
class OrderTodo extends Model
{
    /**
     * @param $value
     * @return Carbon
     */
    public function getDeadlineAttribute($value) {
        return Carbon::parse($value);
    }

    /**
     * @param $value
     * @return Carbon|null
     */
    public function getCompletedAtAttribute($value) {
        return $value != null ? Carbon::parse($value) : null;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function order() {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    /**
     * @return bool
     */
    public function isCompleted() {
        return $this->completed_at != null;
    }
}
