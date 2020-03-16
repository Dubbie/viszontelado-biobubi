<?php

namespace App\Subesz;


use App\Order;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    public function __construct()
    {

    }

    public function getOrders() {
        if (Auth::user()->admin) {
            return Order::orderBy('created_at', 'desc')->get();
        }

        // Csak a saját cuccait szedjük ki
        $zips = array_column(Auth::user()->zips->toArray(), 'zip');
        return Order::whereIn('shipping_postcode', $zips)->orderBy('created_at', 'desc')->get();
    }

    public function getFormattedAddress($order) {
        $out = '';

        if ($order->shippingPostcode && $order->shippingCity && $order->shippingAddress1) {
            $out = sprintf('%s %s, %s', $order->shippingPostcode, $order->shippingCity, $order->shippingAddress1);
        }

        return $out;
    }
}