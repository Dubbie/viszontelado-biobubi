<?php

namespace App\Http\Controllers;

use App\Order;
use App\Subesz\ShoprenterService;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /** @var ShoprenterService */
    private $shoprenter;

    /**
     * OrderController constructor.
     * @param ShoprenterService $shoprenterService
     */
    public function __construct(ShoprenterService $shoprenterService)
    {
        $this->shoprenter = $shoprenterService;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index() {
        $orders = $this->shoprenter->getAllOrders();

        return view('order.index')->with([
            'orders' => $orders,
        ]);
    }

    /**
     * @param $orderId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($orderId) {
        $order = $this->shoprenter->getOrder($orderId);
        $statuses = $this->shoprenter->getAllStatuses();

        return view('order.show')->with([
            'order' => $order,
            'statuses' => $statuses->items,
        ]);
    }

    /**
     * @param Request $request
     */
    public function handleWebhook(Request $request) {
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

            $order->save();
        }
    }

    public function updateStatus(Request $request) {
        $data = $request->validate([
           'order-id' => 'required',
           'order-status-href' => 'required',
        ]);

        $statusId = str_replace(sprintf('%s/orderStatuses/', env('SHOPRENTER_API')), '', $data['order-status-href']);
        dd($this->shoprenter->updateOrder($data['order-id'], $statusId));
    }
}
