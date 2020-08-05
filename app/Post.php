<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Post
 * @package App
 * @mixin Post
 */
class Post extends Model
{
    public function author() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @return mixed
     */
    public function getThumbnailUrl() {
        return str_replace('public/', '', url('storage/' . $this->thumbnail_path));
    }
}
