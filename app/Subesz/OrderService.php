<?php

namespace App\Subesz;


use App\Order;
use App\User;
use App\UserZip;
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
    public function getOrdersFiltered($filter)
    {
        $user = User::find(Auth::user()->id);

        // Csak adminnak engedjük a reseller szűrést...
        if (array_key_exists('reseller', $filter) && Auth::user()->admin) {
            $user = User::find($filter['reseller']);
        }

        // Ha admin és van irányítószáma akkor nézzük meg, hogy mik azok a megrendelések amikhez nincs viszonteladói irányítószám
        $userZips = array_column($user->zips->toArray(), 'zip');
        $resellerZips = array_column(UserZip::select('zip')->whereNotIn('zip', $userZips)->get()->toArray(), 'zip');

        $query = (new Order())->newQuery();

        // Filter
        if (array_key_exists('query', $filter)) {
            $searchValue = '%' . $filter['query'] . '%';
            $query = $query->where('firstname', 'like', $searchValue)
                ->orWhere('lastname', 'like', $searchValue)
                ->orWhere('shipping_address', 'like', $searchValue)
                ->orWhere('email', 'like', $searchValue);
        }

        // Státusz
        if (array_key_exists('status', $filter)) {
            $query = $query->where('status_text', $filter['status']);
        }

        if ($user->admin && count($user->zips) == 0) {
            return $query->orderBy('created_at', 'desc')->get();
        } else if ($user->admin && count($user->zips) > 0) {
            // Kiszedjük azokat amik megfeleltek a feltételeknek
            return $query->whereIn('shipping_postcode', $userZips)->orWhereNotIn('shipping_postcode', $resellerZips)->orderBy('created_at', 'desc')->get();
        } else {
            // Kiszedjük azokat amik megfeleltek a feltételeknek
            return $query->whereIn('shipping_postcode', $userZips)->orderBy('created_at', 'desc')->get();
        }
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

    public function getFormattedAddress($order)
    {
        $out = '';

        if ($order->shippingPostcode && $order->shippingCity && $order->shippingAddress1) {
            $out = sprintf('%s %s, %s', $order->shippingPostcode, $order->shippingCity, $order->shippingAddress1);
        }

        return $out;
    }
}