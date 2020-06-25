<?php

namespace App\Http\Controllers;

use App\Mail\NewOrder;
use App\Order;
use App\Subesz\BillingoService;
use App\Subesz\OrderService;
use App\Subesz\ShoprenterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShoprenterController extends Controller
{
    /** @var ShoprenterService */
    private $shoprenterApi;

    /** @var OrderService */
    private $orderService;

    /** @var BillingoService */
    private $billingoService;

    /**
     * ShoprenterController constructor.
     * @param OrderService $orderService
     * @param ShoprenterService $shoprenterService
     * @param BillingoService $billingoService
     */
    public function __construct(OrderService $orderService, ShoprenterService $shoprenterService, BillingoService $billingoService)
    {
        $this->orderService = $orderService;
        $this->shoprenterApi = $shoprenterService;
        $this->billingoService = $billingoService;
    }

    /**
     * @param $privateKey
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateOrders($privateKey)
    {
        // Ellenőrizzük a kulcsot
        if (env('PRIVATE_KEY') != $privateKey) {
            Log::error('-- Hiba a Shoprenterből való frissítéskor, nem egyezett a privát kulcs --');
            return redirect(action('OrderController@index'))->with([
                'error' => 'Hibás privát kulcs lett megadva',
            ]);
        }

        Log::info('-- Shoprenter API-ból frissítés megkezdése --');
        $start = Carbon::now();
        $osds = $this->shoprenterApi->getAllStatuses();

        $statusMap = [];
        foreach ($osds->items as $osd) {
            $orderStatusId = str_replace(sprintf('%s/orderStatuses/', env('SHOPRENTER_API')), '', $osd->orderStatus->href);

            $statusMap[$orderStatusId] = [
                'name' => $osd->name,
                'color' => $osd->color,
            ];
        }

        $orders = $this->shoprenterApi->getAllOrders();
        $successCount = 0;
        $orderResources = [];
        foreach ($orders as $order) {
            $orderResources[] = $order->id;

            $muted = true;
            if ($local = $this->orderService->updateLocalOrder($order, $muted)) {
                $successCount++;
            }
        }

        // Töröljük ki azokat amik már nincsenek a rendszerbe
        Order::whereNotIn('inner_resource_id', $orderResources)->delete();

        if ($successCount == count($orders)) {
            $elapsed = $start->floatDiffInSeconds();
            Log::info(sprintf('--- %s db megrendelés sikeresen frissítve (Eltelt idő: %ss)', $successCount, $elapsed));
            Log::info('-- Shoprenter API-ból frissítés vége --');
            return redirect(action('OrderController@index'))->with([
                'success' => sprintf('%s db megrendelés sikeresen frissítve (Eltelt idő: %ss)', $successCount, $elapsed),
            ]);
        } else {
            return redirect(action('OrderController@index'))->with([
                'error' => 'Hiba történt a megrendelések frissítésekor',
            ]);
        }
    }

    /**
     * @param $privateKey
     * @param Request $request
     * @return array
     */
    public function handleWebhook($privateKey, Request $request)
    {
        Log::info('- Shoprenter Új Megrendelés Webhook -');

        // Ellenőrizzük a kulcsot
        if (env('PRIVATE_KEY') != $privateKey) {
            return ['error' => 'Hibás privát kulcs lett megadva'];
        }

        $array = json_decode($request->input('data'), true);
        Log::info(sprintf('-- Megrendelések száma: %s db', count($array['orders']['order'])));
        foreach ($array['orders']['order'] as $_order) {
            Log::info($_order);

            // Elmentése a Megrendelésnek db-be
            $localOrder = new Order();
            $localOrder->shipping_postcode = $_order['shippingPostcode'];
            $localOrder->shipping_city = $_order['shippingCity'];
            $localOrder->shipping_address = sprintf('%s %s', $_order['shippingAddress1'], $_order['shippingAddress2']);
            $localOrder->inner_id = $_order['innerId'];
            $localOrder->inner_resource_id = $_order['innerResourceId'];
            $localOrder->total = $_order['total'];
            $localOrder->total_gross = $_order['totalGross'];
            $localOrder->tax_price = $_order['taxPrice'];
            $localOrder->firstname = $_order['firstname'];
            $localOrder->lastname = $_order['lastname'];
            $localOrder->email = $_order['email'];
            $localOrder->shipping_method_name = $_order['shippingMethodName'];
            $localOrder->payment_method_name = $_order['paymentMethodName'];
            $localOrder->status_text = $_order['orderHistory']['statusText'];
            $localOrder->status_color = '#ff00ff';
            $localOrder->created_at = date('Y-m-d H:i:s');

            if (!$localOrder->save()) {
                return ['success' => false];
            }

            $orderId = str_replace('orders/', '', $_order['innerResourceId']);
            $order = $this->shoprenterApi->getOrder($orderId);

            // Mentsük el a számlát
            /** @var Order $localOrder */
            $reseller = $localOrder->getReseller()['correct'];
            Log::info('Hozzátartozó számlázó fiók neve: ' . $reseller->name);

            // Elküldjük róla a levelet is
            if ($reseller->email != 'hello@semmiszemet.hu') {
                \Mail::to($reseller)->send(new NewOrder($order, $reseller));
                Log::info('Levél elküldve az alábbi e-mail címre: ' . $reseller->email);
            }

            // Számla
            $invoiceId = $this->billingoService->createInvoiceFromOrder($order, $reseller);

            // Elmentjük a számlát helyileg a megrendelés azonosítója alapján
            if (!$invoiceId) {
                Log::error('Hiba történt a számla létrehozásakor!');
                return ['success' => false];
            } else {
                $this->billingoService->saveInvoice($reseller, $invoiceId, $localOrder->id);
            }
        }

        return ['success' => true];
    }
}
