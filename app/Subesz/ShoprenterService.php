<?php

namespace App\Subesz;


class ShoprenterService
{
    public function getOrdersByPage($page = 0, $limit = 25) {
        $apiUrl = sprintf('%s/orders?full=1&page=%s&limit=%s', env('SHOPRENTER_API'), $page, $limit);

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

    public function getAllOrders() {
        $page = 0;

        $pageOrders = $this->getOrdersByPage($page, 200);
        $pages = $pageOrders->pageCount;
        $orders = $pageOrders->items;

        for ($i = 1; $i < $pages; $i++) {
            usleep(350000);

            $pageOrders = $this->getOrdersByPage($i);
            $orders = array_merge($orders, $pageOrders->items);
        }

        return $orders;
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
        if ($result['order']->orderStatus) {
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
        }

        curl_close($ch);
        return $result;
    }

    public function updateOrder($orderId, $statusId) {
        $apiUrl = sprintf('%s/orders/%s', env('SHOPRENTER_API'), $orderId);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_HTTPHEADER => ['Accept:application/json'],
            CURLOPT_USERPWD => sprintf( '%s:%s', env('SHOPRENTER_USER'), env('SHOPRENTER_PASSWORD')),
            CURLOPT_TIMEOUT => 120,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $data = [
            'data' => [
                'orderStatus' => [
                    'id' => $statusId,
                ]
            ]
        ];

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response);
    }

    public function getAllStatuses() {
        $apiUrl = sprintf('%s/orderStatusDescriptions?full=1&limit=200', env('SHOPRENTER_API'));

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_HTTPHEADER => ['Content-Type:application/json', 'Accept:application/json'],
            CURLOPT_USERPWD => sprintf('%s:%s', env('SHOPRENTER_USER'), env('SHOPRENTER_PASSWORD')),
            CURLOPT_TIMEOUT => 120,
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response);
    }

    public function getStatusByHref($orderStatusHref) {
        $apiUrl = sprintf('%s/orderStatus?full=1&limit=200', env('SHOPRENTER_API'));

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_HTTPHEADER => ['Content-Type:application/json', 'Accept:application/json'],
            CURLOPT_USERPWD => sprintf('%s:%s', env('SHOPRENTER_USER'), env('SHOPRENTER_PASSWORD')),
            CURLOPT_TIMEOUT => 120,
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response);
    }
}