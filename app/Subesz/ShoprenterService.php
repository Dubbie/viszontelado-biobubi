<?php

namespace App\Subesz;


class ShoprenterService
{
    public function getOrders() {
        $apiUrl = sprintf('%s/orders?full=1&limit=200', env('SHOPRENTER_API'));

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_HTTPHEADER => ['Content-Type:application/json', 'Accept:application/json'],
            CURLOPT_USERPWD => sprintf('%s:%s', env('SHOPRENTER_USER'), env('SHOPRENTER_PASSWORD')),
            CURLOPT_TIMEOUT => 120,
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $return = curl_exec($ch);
        curl_close($ch);

        return json_decode($return);
    }

    public function getOrder($orderId) {
        $apiUrl = sprintf('%s/orders/%s', env('SHOPRENTER_API'), $orderId);
        $result = [];

        // Megrendelés lekérése
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_HTTPHEADER => ['Content-Type:application/json', 'Accept:application/json'],
            CURLOPT_USERPWD => sprintf('%s:%s', env('SHOPRENTER_USER'), env('SHOPRENTER_PASSWORD')),
            CURLOPT_TIMEOUT => 120,
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $result['order'] = json_decode(curl_exec($ch));

        // Státusz lekérése
        curl_setopt_array($ch, [
            CURLOPT_URL => $result['order']->orderStatus->href,
            CURLOPT_HTTPHEADER => ['Content-Type:application/json', 'Accept:application/json'],
            CURLOPT_USERPWD => sprintf('%s:%s', env('SHOPRENTER_USER'), env('SHOPRENTER_PASSWORD')),
            CURLOPT_TIMEOUT => 120,
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $result['status'] = json_decode(curl_exec($ch));

        // Státusz lekérése
        curl_setopt_array($ch, [
            CURLOPT_URL => $result['status']->orderStatusDescriptions->href . '&full=1',
            CURLOPT_HTTPHEADER => ['Content-Type:application/json', 'Accept:application/json'],
            CURLOPT_USERPWD => sprintf('%s:%s', env('SHOPRENTER_USER'), env('SHOPRENTER_PASSWORD')),
            CURLOPT_TIMEOUT => 120,
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $result['statusDescription'] = json_decode(curl_exec($ch))->items[0];

        curl_close($ch);
        return $result;
    }
}