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
        $orders = $this->shoprenter->getOrders();

        return view('order.index')->with([
            'orders' => $orders->items,
        ]);
    }

    /**
     * @param $orderId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($orderId) {
        $order = $this->shoprenter->getOrder($orderId);

        dd($order);
        return view('order.show')->with([
            'order' => $order,
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
            $order->postcode = $_order['shippingPostcode'];
            $order->inner_id = $_order['innerId'];
            $order->inner_resource_id = $_order['innerResourceId'];
            $order->total = $_order['total'];
            $order->total_gross = $_order['totalGross'];
            $order->tax_price = $_order['taxPrice'];

            $order->save();
        }
    }
}
