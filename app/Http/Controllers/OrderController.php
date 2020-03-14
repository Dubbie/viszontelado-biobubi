<?php

namespace App\Http\Controllers;

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
        foreach ($request->all() as $k => $v) {
            Log::info(sprintf('-- %s: %v', $k, $v));
        }
    }
}
