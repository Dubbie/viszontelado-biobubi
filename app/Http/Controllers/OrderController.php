<?php

namespace App\Http\Controllers;

use App\Delivery;
use App\Order;
use App\Subesz\OrderService;
use App\Subesz\ShoprenterService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /** @var ShoprenterService */
    private $shoprenter;

    /** @var OrderService */
    private $orderService;

    /**
     * OrderController constructor.
     * @param ShoprenterService $shoprenterService
     * @param OrderService $orderService
     */
    public function __construct(ShoprenterService $shoprenterService, OrderService $orderService)
    {
        $this->shoprenter = $shoprenterService;
        $this->orderService = $orderService;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $filter = [];

        if ($request->has('filter-reseller')) {
            $filter['reseller'] = $request->input('filter-reseller');
        }
        if ($request->has('filter-status')) {
            $filter['status'] = $request->input('filter-status');
        }
        if ($request->has('filter-query')) {
            $filter['query'] = $request->input('filter-query');
        }

        $orders = $this->orderService->getOrdersFiltered($filter);

        $resellers = [];
        foreach (User::all() as $u) {
            if ($u->id == Auth::id()) {
                continue;
            }

            if (count($u->zips) > 0) {
                $resellers[] = $u;
            }
        }

        $lastUpdate = [
            'datetime' => $this->orderService->getLastUpdate(),
            'human' => $this->orderService->getLastUpdateHuman(),
        ];

        return view('order.index')->with([
            'orders' => $orders,
            'resellers' => $resellers,
            'statuses' => $this->shoprenter->getAllStatuses()->items,
            'filter' => $filter,
            'lastUpdate' => $lastUpdate,
        ]);
    }

    /**
     * @param $orderId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($orderId)
    {
        $order = $this->shoprenter->getOrder($orderId);
        $this->shoprenter->updateLocalOrder($order['order']);

        return view('order.show')->with([
            'order' => $order,
        ]);
    }

    /**
     * @param $orderId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showStatus($orderId)
    {
        $order = $this->shoprenter->getOrder($orderId);
        $statuses = $this->shoprenter->getAllStatuses();

        return view('inc.order-status-content')->with([
            'order' => $order,
            'statuses' => $statuses->items,
        ]);
    }

    /**
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
            // Elmentése a Megrendelésnek db-be
            $order = new Order();
            $order->shipping_postcode = $_order['shippingPostcode'];
            $order->shipping_city = $_order['shippingCity'];
            $order->shipping_address = sprintf('%s %s', $_order['shippingAddress1'], $_order['shippingAddress2']);
            $order->inner_id = $_order['innerId'];
            $order->inner_resource_id = $_order['innerResourceId'];
            $order->total = $_order['total'];
            $order->total_gross = $_order['totalGross'];
            $order->tax_price = $_order['taxPrice'];
            $order->firstname = $_order['firstname'];
            $order->lastname = $_order['lastname'];
            $order->email = $_order['email'];
            $order->shipping_method_name = $_order['shippingMethodName'];
            $order->payment_method_name = $_order['paymentMethodName'];
            $order->status_text = $_order['orderHistory']['statusText'];
            $order->created_at = date('Y-m-d H:i:s', strtotime($_order['orderCreated']));

            $order->save();
        }

        return ['success' => true];
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateStatus(Request $request)
    {
        $data = $request->validate([
            'order-id' => 'required',
            'order-status-now' => 'required',
            'order-status-href' => 'required',
            'notify-customer' => 'nullable'
        ]);

        // Ellenőrizzük le, hogy változott-e a státusz href
        if ($data['order-status-href'] == $data['order-status-now']) {
            return redirect(url()->previous())->with([
                'error' => 'A megrendelés státusza már a kiválasztott státuszban van',
            ]);
        }

        $statusId = str_replace(sprintf('%s/orderStatuses/', env('SHOPRENTER_API')), '', $data['order-status-href']);

        if ($this->shoprenter->updateOrderStatusId($data['order-id'], $statusId)) {
            // Kiszállítva állító
            if ($data['order-status-href'] == sprintf('%s/orderStatuses/b3JkZXJTdGF0dXMtb3JkZXJfc3RhdHVzX2lkPTU=', env('SHOPRENTER_API'))) {
                $delivery = new Delivery();
                $delivery->user_id = Auth::id();
                $delivery->order_id = $this->orderService->getLocalOrderByResourceId($data['order-id'])->id;
                $delivery->save();
            } else {
                $delivery = Delivery::where('order_id', $this->orderService->getLocalOrderByResourceId($data['order-id'])->id);
                if ($delivery) {
                    $delivery->delete();
                }
            }

            return redirect(action('OrderController@show', ['orderId' => $data['order-id']]))->with([
                'success' => 'Állapot sikeresen frissítve',
            ]);
        }

        return redirect(action('OrderController@show', ['orderId' => $data['order-id']]))->with([
            'error' => 'Ismeretlen hiba történt az állapot frissítésekor',
        ]);
    }
}
