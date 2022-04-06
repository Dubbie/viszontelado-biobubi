<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerComment extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
