<?php

namespace App\Subesz;


use App\Order;
use App\User;
use App\UserZip;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct()
    {

    }

    /**
     * @param $filter
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getOrdersFiltered($filter = [])
    {
        $user = User::find(Auth::user()->id);

        // Csak adminnak engedjük a reseller szűrést...
        if (array_key_exists('reseller', $filter) && Auth::user()->admin) {
            $user = User::find($filter['reseller']);
        }

        // Ha admin és van irányítószáma akkor nézzük meg, hogy mik azok a megrendelések amikhez nincs viszonteladói irányítószám
        $userZips = array_column($user->zips->toArray(), 'zip');
        $resellerZips = array_column(UserZip::select('zip')->whereNotIn('zip', $userZips)->get()->toArray(), 'zip');

        $orders = Order::query();

        if ($user->admin && count($user->zips) > 0) {
            // Kiszedjük azokat amik megfeleltek a feltételeknek
            $orders = $orders->where(function ($query) use ($userZips, $resellerZips) {
               $query->whereIn('shipping_postcode', $userZips)->orWhereNotIn('shipping_postcode', $resellerZips);
            });
        } else if (!$user->admin) {
            $orders = $orders->where(function ($query) use ($userZips) {
               $query->whereIn('shipping_postcode', $userZips)->orderBy('created_at', 'desc');
            });
        }

        // Filter
        if (array_key_exists('query', $filter)) {
            $searchValue = '%' . $filter['query'] . '%';
            $orders = $orders->where(function ($query) use ($searchValue) {
                $query->where('firstname', 'like', $searchValue)
                    ->orWhere('lastname', 'like', $searchValue)
                    ->orWhere('shipping_address', 'like', $searchValue)
                    ->orWhere('email', 'like', $searchValue);
            });
        }

        // Státusz
        if (array_key_exists('status', $filter)) {
            $orders = $orders->where('status_text', '=', $filter['status']);
        }

        return $orders->orderBy('created_at', 'desc')->get();
    }

    /**
     * @param $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getOrdersQueryByUserId($userId) {
        $user = User::find(Auth::user()->id);

        $userZips = array_column($user->zips->toArray(), 'zip');
        $resellerZips = array_column(UserZip::select('zip')->whereNotIn('zip', $userZips)->get()->toArray(), 'zip');

        $orders = Order::query();

        if ($user->admin && count($user->zips) > 0) {
            // Kiszedjük azokat amik megfeleltek a feltételeknek
            $orders = $orders->where(function ($query) use ($userZips, $resellerZips) {
                $query->whereIn('shipping_postcode', $userZips)->orWhereNotIn('shipping_postcode', $resellerZips);
            });
        } else if (!$user->admin) {
            $orders = $orders->where(function ($query) use ($userZips) {
                $query->whereIn('shipping_postcode', $userZips)->orderBy('created_at', 'desc');
            });
        }

        return $orders;
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function getOrdersByUserId($userId)
    {
        $user = User::find($userId);

        // Ha admin és van irányítószáma akkor nézzük meg, hogy mik azok a megrendelések amikhez nincs viszonteladói irányítószám
        $userZips = array_column($user->zips->toArray(), 'zip');
        $resellerZips = array_column(UserZip::select('zip')->whereNotIn('zip', $userZips)->get()->toArray(), 'zip');

        if ($user->admin && count($user->zips) == 0) {
            return Order::orderBy('created_at', 'desc')->get();
        } else if ($user->admin && count($user->zips) > 0) {
            // Kiszedjük azokat amik megfeleltek a feltételeknek
            return Order::whereIn('shipping_postcode', $userZips)->orWhereNotIn('shipping_postcode', $resellerZips)->orderBy('created_at', 'desc')->get();
        } else {
            // Kiszedjük azokat amik megfeleltek a feltételeknek
            return Order::whereIn('shipping_postcode', $userZips)->orderBy('created_at', 'desc')->get();
        }
    }

    /**
     * @return mixed
     */
    public function getLastUpdate() {
        $lastOrder = Order::orderBy('updated_at')->first();

        if (!$lastOrder) {
            return null;
        }

        return $lastOrder->updated_at;
    }

    /**
     * @return string
     */
    public function getLastUpdateHuman() {
        /** @var Carbon $last */
        $last = $this->getLastUpdate();

        if ($last) {
            return $last->diffForHumans();
        }

        return '';
    }

    /**
     * @param $innerId
     * @return mixed
     */
    public function getLocalOrderByResourceId($resourceId) {
        return Order::where('inner_resource_id', $resourceId)->first();
    }

    /**
     * @param $order
     * @return string
     */
    public function getFormattedAddress($order)
    {
        $out = '';

        if ($order->shippingPostcode && $order->shippingCity && $order->shippingAddress1) {
            $out = sprintf('%s %s, %s', $order->shippingPostcode, $order->shippingCity, $order->shippingAddress1);
        }

        return $out;
    }
}