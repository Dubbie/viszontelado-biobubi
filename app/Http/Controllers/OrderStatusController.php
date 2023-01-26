<?php

namespace App\Http\Controllers;

use App\OrderStatus;
use App\Subesz\OrderService;
use App\Subesz\StatusService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderStatusController extends Controller
{
    /** @var \App\Subesz\StatusService */
    private $statusService;

    /** @var \App\Subesz\OrderService */
    private $orderService;

    /**
     * OrderStatusController constructor.
     *
     * @param  \App\Subesz\StatusService  $statusService
     * @param  \App\Subesz\OrderService   $orderService
     */
    public function __construct(StatusService $statusService, OrderService $orderService) {
        $this->statusService = $statusService;
        $this->orderService  = $orderService;
    }

    /**
     * Frissíti a státuszokat Shoprenter-ből, de az AKTÍV állapotot nem módosítja
     *
     * @param $privateKey
     * @return bool
     */
    public function updateStatuses($privateKey): bool {
        // Ellenőrizzük a kulcsot
        if (env('PRIVATE_KEY') != $privateKey) {
            Log::error('-- Hiba a Shoprenterből való frissítéskor, nem egyezett a privát kulcs --');

            return false;
        }

        Log::info('-- Státuszok frissítésének megkezdése... --');
        $start = Carbon::now();
        $osds  = resolve('App\Subesz\ShoprenterService')->getAllStatuses();
        if (! $osds || ! property_exists($osds, 'items')) {
            Log::error('A Shoprenter API nem tért vissza eredményekkel');

            return false;
        }

        foreach ($osds->items as $item) {
            $statusId = str_replace(env('SHOPRENTER_API').'/orderStatuses/', '', $item->orderStatus->href);
            /** @var OrderStatus $os */
            $os            = OrderStatus::where('status_id', $statusId)->first() ?? new OrderStatus();
            $os->status_id = $statusId;
            $os->name      = $item->name;
            $os->color     = $item->color;
            $os->save();
        }

        $elapsed = $start->floatDiffInSeconds();
        Log::info(sprintf('--- Státuszok sikeresen frissítve (Eltelt idő: %ss)', $elapsed));
        Log::info('-- ... Shoprenter státuszok API-ból való frissítésének vége --');

        return true;
    }

    /**
     * @param  string                    $privateKey
     * @param  \Illuminate\Http\Request  $request
     * @return bool|string[]
     */
    public function handleStatusWebhook(string $privateKey, Request $request) {
        Log::info('|-------------------------------------|');
        Log::info('| Shoprenter Státusz változás Webhook |');
        Log::info('|-------------------------------------|');

        // Ellenőrizzük a kulcsot
        if (env('PRIVATE_KEY') != $privateKey) {
            return ['error' => 'Hibás privát kulcs lett megadva'];
        }

        $array = json_decode($request->input('data'), true);
        Log::info(sprintf('-- Megrendelések száma: %s db', count($array['orders']['order'])));
        foreach ($array['orders']['order'] as $_order) {
            $os              = $this->statusService->getOrderStatusByName($_order['orderHistory']['statusText']);
            $innerResourceId = str_replace('orders/', '', $_order['innerResourceId']);
            $srOrder         = resolve('App\Subesz\ShoprenterService')->getOrder($innerResourceId);

            /** @var \App\Order $localOrder */
            $localOrder = $this->orderService->getLocalOrderByResourceId($innerResourceId);
            if ($localOrder && $localOrder->status_text == $os->name) {
                Log::warning('Már a megfelelő státuszban van a megrendelés, de nem érdekel minket.');
                Log::info('Régi: '.$localOrder->status_text);
                Log::info('Új: '.$os->name);
            }

            // Frissítjük most a helyi megrendelésünket
            $this->orderService->updateLocalOrder($srOrder['order']);

            // Lekezeljük a státusz változást
            $response = $this->statusService->handleNewStatus($srOrder, $this->orderService->getLocalOrderByResourceId($innerResourceId), $os);
        }
    }
}
