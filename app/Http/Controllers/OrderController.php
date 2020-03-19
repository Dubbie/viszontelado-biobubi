<?php

namespace App\Http\Controllers;

use App\Delivery;
use App\Order;
use App\Subesz\BillingoService;
use App\Subesz\OrderService;
use App\Subesz\ShoprenterService;
use App\User;
use Billingo\API\Connector\Exceptions\JSONParseException;
use Billingo\API\Connector\Exceptions\RequestErrorException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /** @var ShoprenterService */
    private $shoprenterApi;

    /** @var OrderService */
    private $orderService;

    /** @var BillingoService */
    private $billingoService;

    /**
     * OrderController constructor.
     * @param ShoprenterService $shoprenterService
     * @param OrderService $orderService
     * @param BillingoService $billingoService
     */
    public function __construct(ShoprenterService $shoprenterService, OrderService $orderService, BillingoService $billingoService)
    {
        $this->shoprenterApi = $shoprenterService;
        $this->orderService = $orderService;
        $this->billingoService = $billingoService;
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
            'statuses' => $this->shoprenterApi->getAllStatuses()->items,
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
        $order = $this->shoprenterApi->getOrder($orderId);
        $invoice = $this->billingoService->makeDataFromOrder($order);

        // Kezeljük le a státusz frissítéskor létrejövő session-t
        if (!session()->has('updateLocalOrder') || session('updateLocalOrder') != false) {
            $this->orderService->updateLocalOrder($order['order']);
        }

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
        $order = $this->shoprenterApi->getOrder($orderId);
        $statuses = $this->shoprenterApi->getAllStatuses();

        return view('inc.order-status-content')->with([
            'order' => $order,
            'statuses' => $statuses->items,
        ]);
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
        ]);

        // Ellenőrizzük le, hogy változott-e a státusz href
        if ($data['order-status-href'] == $data['order-status-now']) {
            return redirect(url()->previous())->with([
                'error' => 'A megrendelés státusza már a kiválasztott státuszban van',
            ]);
        }

        $statusId = str_replace(sprintf('%s/orderStatuses/', env('SHOPRENTER_API')), '', $data['order-status-href']);

        if ($this->shoprenterApi->updateOrderStatusId($data['order-id'], $statusId)) {
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

            // Flasheljük be, hogy most nem frissülünk
            $request->session()->flash('updateLocalOrder', false);

            // Mehet a redirect
            return redirect(action('OrderController@show', ['orderId' => $data['order-id']]))->with([
                'success' => 'Állapot sikeresen frissítve',
            ]);
        }

        return redirect(action('OrderController@show', ['orderId' => $data['order-id']]))->with([
            'error' => 'Ismeretlen hiba történt az állapot frissítésekor',
        ]);
    }
}
