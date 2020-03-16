<?php

namespace App\Subesz;


use App\Order;
use App\UserZip;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    public function __construct()
    {

    }

    public function getOrders() {
        if (Auth::user()->admin && count(Auth::user()->zips) == 0) {
            return Order::orderBy('created_at', 'desc')->get();
        }
        // Ha admin és van irányítószáma akkor nézzük meg, hogy mik azok a megrendelések amikhez nincs viszonteladói irányítószám
        $userZips = array_column(Auth::user()->zips->toArray(), 'zip');
        $resellerZips = array_column(UserZip::select('zip')->whereNotIn('zip', $userZips)->get()->toArray(), 'zip');
//        dd($resellerZips);
        // Csak a saját cuccait szedjük ki
        $zips = array_column(Auth::user()->zips->toArray(), 'zip');
        return Order::whereIn('shipping_postcode', $zips)->orWhereNotIn('shipping_postcode', $resellerZips)->orderBy('created_at', 'desc')->get();
    }

    public function getFormattedAddress($order) {
        $out = '';

        if ($order->shippingPostcode && $order->shippingCity && $order->shippingAddress1) {
            $out = sprintf('%s %s, %s', $order->shippingPostcode, $order->shippingCity, $order->shippingAddress1);
        }

        return $out;
    }
}