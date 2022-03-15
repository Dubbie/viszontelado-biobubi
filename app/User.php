<?php

namespace App;

use App\Subesz\CustomerService;
use App\Subesz\OrderService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 *
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
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
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
     * @return HasMany
     */
    public function zips(): HasMany
    {
        return $this->hasMany(UserZip::class, 'user_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function stock(): HasMany
    {
        return $this->hasMany(Stock::class, 'user_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'reseller_id', 'id');
    }

    /**
     * Visszaadja a felhasználóhoz tartozó megrendeléseket
     *
     * @return Builder[]|Collection
     */
    public function getOrders()
    {
        /** @var OrderService $orderService */
        $orderService = resolve('App\Subesz\OrderService');

        return $orderService->getOrdersFiltered(['reseller' => $this->id]);
    }

    /**
     * @return Builder[]|Collection
     */
    public function getOrdersWithProducts()
    {
        /** @var OrderService $orderService */
        $orderService = resolve('App\Subesz\OrderService');

        return $orderService->getOrdersFiltered([
            'reseller' => $this->id,
            'with_products' => true,
        ]);
    }

    /**
     * @return HasMany
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'user_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function todos(): HasMany
    {
        return $this->hasMany(OrderTodo::class, 'user_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function details(): HasOne
    {
        return $this->hasOne(UserDetails::class, 'user_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class, 'user_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'user_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function worksheet(): HasMany
    {
        return $this->hasMany(Worksheet::class, 'user_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function marketingResults(): HasMany
    {
        return $this->hasMany(MarketingResult::class, 'user_id', 'id');
    }

    /**
     * Visszaadja, hogy a felhasználó AAM-es-e.
     *
     * @return bool
     */
    public function isAAM(): bool
    {
        return $this->vat_id == env('AAM_VAT_ID');
    }

    /**
     * @return int
     */
    public function getDeliveryCountThisMonth(): int
    {
        $start = Carbon::now()->firstOfMonth();
        $end = Carbon::now()->endOfDay();

        return $this->deliveries()->whereBetween('delivered_at', [$start, $end])->count();
    }

    /**
     * @return HasMany
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'user_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'user_id', 'id');
    }

    /**
     * Visszaadja a felhasználóhoz tartozó megrendeléseket
     *
     * @return LengthAwarePaginator
     */
    public function getCustomers(): LengthAwarePaginator
    {
        /** @var CustomerService $orderService */
        $orderService = resolve('App\Subesz\CustomerService');

        return $orderService->getCustomersFiltered(['reseller' => $this->id]);
    }

    /**
     * @param  bool  $withSuffix
     * @return string
     */
    public function getFormattedBalance($withSuffix = false)
    {
        $balanceOutput = number_format($this->balance, 0, ' ', ' ');

        return $withSuffix ? $balanceOutput.' Ft' : $balanceOutput;
    }
}
