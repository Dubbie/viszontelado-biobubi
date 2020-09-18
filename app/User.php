<?php

namespace App;

use App\Subesz\OrderService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 * @package App
 * @mixin User
 */
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function zips() {
        return $this->hasMany(UserZip::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stock() {
        return $this->hasMany(Stock::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders() {
        return $this->hasMany(Order::class, 'reseller_id', 'id');
    }

    /**
     * Visszaadja a felhasználóhoz tartozó megrendeléseket
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getOrders() {
        /** @var OrderService $orderService */
        $orderService = resolve('App\Subesz\OrderService');

        return $orderService->getOrdersFiltered(['reseller' => $this->id]);
    }

    public function getOrdersWithProducts() {
        /** @var OrderService $orderService */
        $orderService = resolve('App\Subesz\OrderService');

        return $orderService->getOrdersFiltered([
            'reseller' => $this->id,
            'with_products' => true,
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deliveries() {
        return $this->hasMany(Delivery::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function expenses() {
        return $this->hasMany(Expense::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function todos() {
        return $this->hasMany(OrderTodo::class, 'user_id', 'id');
    }
}
