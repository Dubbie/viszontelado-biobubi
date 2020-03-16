<?php

namespace App\Http\Controllers;

use App\Order;
use App\Subesz\OrderService;
use App\Subesz\ShoprenterService;
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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $orders = $this->orderService->getOrdersByUserId(Auth::user()->id);

        return view('order.index')->with([
            'orders' => $orders,
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
     */
    public function handleWebhook(Request $request)
    {
        Log::info('- Shoprenter Új Megrendelés Webhook -');
        $array = json_decode($request->input('data'), true);
        Log::info(sprintf('-- Megrendelések száma: %s db', count($array['orders']['order'])));
        foreach ($array['orders']['order'] as $_order) {
            Log::info(dump($_order));

            // Elmentése a Megrendelésnek db-be
            $order = new Order();
            $order->shipping_postcode = $_order['shippingPostcode'];
            $order->shipping_city = $_order['shippingCity'];
            $order->shipping_address = $_order['shippingAddress1'];
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
    }

    public function updateStatus(Request $request)
    {
        $data = $request->validate([
            'order-id' => 'required',
            'order-status-href' => 'required',
            'notify-customer' => 'nullable'
        ]);

        $statusId = str_replace(sprintf('%s/orderStatuses/', env('SHOPRENTER_API')), '', $data['order-status-href']);

        if ($this->shoprenter->updateOrderStatusId($data['order-id'], $statusId)) {
            return redirect(url()->previous())->with([
                'success' => 'Állapot sikeresen frissítve',
            ]);
        }

        return redirect(url()->previous())->with([
            'error' => 'Ismeretlen hiba történt az állapot frissítésekor',
        ]);
    }
}
