<?php

namespace App\Subesz;

use App\User;

class UserService
{
    /**
     * @return \App\User[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     * @comment Visszaadja a viszonteladókat.
     */
    public function getResellers() {
        return User::whereHas('regions')->get();
    }

    /**
     * @return \App\User[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     * @comment Visszaadja a viszonteladókat regions_count mezővel együtt.
     */
    public function getResellersWithRegionsCount() {
        return User::whereHas('regions')->withCount('regions')->get();
    }
}