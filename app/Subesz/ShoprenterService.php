<?php

namespace App\Subesz;


use App\Order;
use Illuminate\Support\Facades\Log;

class ShoprenterService
{
    /** @var array */
    private $statusMap;

    /**
     * ShoprenterService constructor.
     */
    public function __construct()
    {

        $osds = $this->getAllStatuses();

        foreach ($osds->items as $osd) {
            $orderStatusId = str_replace(sprintf('%s/orderStatuses/', env('SHOPRENTER_API')), '', $osd->orderStatus->href);

            $this->statusMap[$orderStatusId] = [
                'name' => $osd->name,
                'color' => $osd->color,
            ];
        }
    }

    /**
     * Visszad egy oldalnyi megrendelést a megadottak alapján
     *
     * @param int $page
     * @param int $limit
     * @return mixed
     */
    public function getOrdersByPage($page = 0, $limit = 25) {
        $apiUrl = sprintf('%s/orders?excludeAbandonedCart=1&full=1&page=%s&limit=%s', env('SHOPRENTER_API'), $page, $limit);

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

    /**
     * Visszaadja az összes megrendelést
     *
     * @return array
     */
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

    /**
     * Visszaadja a megrendelés részleteit
     *
     * @param $orderId
     * @return array
     */
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
        if (property_exists($result['order'], 'orderStatus') && $result['order']->orderStatus) {
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

        // Termékek lekérése
        if ($result['order']->orderProducts) {
            curl_setopt_array($ch, [
                CURLOPT_URL => $result['order']->orderProducts->href . '&full=1',
                CURLOPT_HTTPHEADER => ['Content-Type:application/json', 'Accept:application/json'],
                CURLOPT_USERPWD => sprintf('%s:%s', env('SHOPRENTER_USER'), env('SHOPRENTER_PASSWORD')),
                CURLOPT_TIMEOUT => 120,
                CURLOPT_RETURNTRANSFER => true,
            ]);
            $result['products'] = json_decode(curl_exec($ch));
        }

        // order gift wrappings:
        if (property_exists($result['order'], 'orderTotals') && $result['order']->orderTotals) {
            curl_setopt_array($ch, [
                CURLOPT_URL => $result['order']->orderTotals->href . '&full=1',
                CURLOPT_HTTPHEADER => ['Content-Type:application/json', 'Accept:application/json'],
                CURLOPT_USERPWD => sprintf('%s:%s', env('SHOPRENTER_USER'), env('SHOPRENTER_PASSWORD')),
                CURLOPT_TIMEOUT => 120,
                CURLOPT_RETURNTRANSFER => true,
            ]);
            $result['totals'] = json_decode(curl_exec($ch))->items;
        }

        curl_close($ch);
        return $result;
    }

    /**
     * @param $orderId
     * @param $statusId
     * @return bool|mixed
     */
    public function updateOrderStatusId($orderId, $statusId) {
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
        $newOrder = json_decode(curl_exec($ch));
        curl_close($ch);

        // Helyesen frissült a megrendelés
        if (strpos($newOrder->orderStatus->href, $statusId) != -1) {
            // Frissítsük a helyi változatot
            /** @var OrderService $orderService */
            $orderService = resolve('App\Subesz\OrderService');
            if ($orderService->updateLocalOrder($newOrder)) {
                return true;
            } else {
                Log::error(sprintf('Hiba történt a helyi megrendelés frissítésekor (Azonosító: %s)'), $newOrder->id);
            }
        }

        return false;
    }

    /**
     * @return mixed
     */
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
}