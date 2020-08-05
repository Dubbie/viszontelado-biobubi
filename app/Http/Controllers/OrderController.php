<?php

namespace App\Http\Controllers;

use App\Delivery;
use App\Mail\RegularOrderCompleted;
use App\Mail\TrialOrderCompleted;
use App\Order;
use App\Subesz\BillingoNewService;
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

    /**
     * OrderController constructor.
     * @param ShoprenterService $shoprenterService
     * @param OrderService $orderService
     */
    public function __construct(ShoprenterService $shoprenterService, OrderService $orderService)
    {
        $this->shoprenterApi = $shoprenterService;
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

        // Kezeljük le a státusz frissítéskor létrejövő session-t
        if (!session()->has('updateLocalOrder') || session('updateLocalOrder') != false) {
            $this->orderService->updateLocalOrder($order['order']);
        }

        return view('order.show')->with([
            'order' => $order,
            'localOrderId' => $this->orderService->getLocalOrderByResourceId($order['order']->id),
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
        /** @var BillingoNewService $bs */
        $bs = resolve('App\Subesz\BillingoNewService');

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
                $orderId = $this->orderService->getLocalOrderByResourceId($data['order-id'])->id;

                $delivery = new Delivery();
                $delivery->user_id = Auth::id();
                $delivery->order_id = $orderId;
                $delivery->save();

                // Kikeressük a helyi megrendelést
                $localOrder = Order::find($orderId);
                $reseller = $localOrder->getReseller()['correct'];

                // Megnézzük, hogy RÉGI számla-e vagy ÚJ
                if (!$localOrder->draft_invoice_id && $localOrder->invoice_path && $localOrder->invoice_id) {
                    // Van számla letöltve, csak küldjük ki
                    $localOrder->sendInvoice();
                } else if (!$localOrder->draft_invoice_id && !$localOrder->invoice_path && $localOrder->invoice_id) {
                    // RÉGI SZÁMLA, nem generálunk csak letöltünk
                    $path = $bs->downloadInvoice($localOrder->invoice_id, $localOrder, $reseller);
                    if (!$path) {
                        return redirect(action('OrderController@show', ['orderId' => $data['order-id']]))->with([
                            'error' => 'Hiba történt a megrendelés állapotának frissítésekor',
                        ]);
                    }

                    // Elmentjük a számlát helyileg
                    $localOrder->invoice_path = $path;
                    $localOrder->save();
                    $localOrder->sendInvoice();
                } else if ($localOrder->draft_invoice_id) {
                    // ÚJ típusú számla, először generáltatunk valós számlát
                    // Létrehozzuk az ÉLES számlát
                    $realInvoice = $localOrder->createRealInvoice();
                    $localOrder->invoice_id = $realInvoice->getId();
                    $localOrder->save();
                    $localOrder->refresh();
                    $path = $bs->downloadInvoice($realInvoice->getId(), $localOrder, $reseller);
                    if (!$path) {
                        return redirect(action('OrderController@show', ['orderId' => $data['order-id']]))->with([
                            'error' => 'Hiba történt a megrendelés állapotának frissítésekor',
                        ]);
                    }

                    // Elmentjük a számlát helyileg
                    $localOrder->invoice_path = $path;
                    $localOrder->save();
                    $localOrder->sendInvoice();
                } else {
                    Log::error('Nincs se régi se új számla azonosító, nem lehet létrehozni számlát automatikusan');
                }
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function massUpdateStatus(Request $request)
    {
        /** @var BillingoNewService $bs */
        $bs = resolve('App\Subesz\BillingoNewService');

        $data = $request->validate([
            'order-ids' => 'required',
            'order-status-href' => 'required',
        ]);

        // Átalakítjuk a bemenetet
        $orderIds = json_decode($data['order-ids']);

        // Átalakítjuk a státusz linkjét, hogy csak az azonosítót kapjuk vissza
        $statusId = str_replace(sprintf('%s/orderStatuses/', env('SHOPRENTER_API')), '', $data['order-status-href']);

        // Végigmegyünk a kijelölésen
        $successCount = 0;
        $shouldDeliver = $statusId == 'b3JkZXJTdGF0dXMtb3JkZXJfc3RhdHVzX2lkPTU=';

        foreach ($orderIds as $orderId) {
            if ($this->shoprenterApi->updateOrderStatusId($orderId, $statusId)) {
                // Kiszállítva állító
                if ($shouldDeliver) {
                    $localOrderId = $this->orderService->getLocalOrderByResourceId($orderId)->id;

                    $delivery = new Delivery();
                    $delivery->user_id = Auth::id();
                    $delivery->order_id = $localOrderId;
                    $delivery->save();

                    // Kikeressük a helyi megrendelést
                    $localOrder = Order::find($localOrderId);
                    $reseller = $localOrder->getReseller()['correct'];

                    // Megnézzük, hogy RÉGI számla-e vagy ÚJ
                    if (!$localOrder->draft_invoice_id && $localOrder->invoice_path && $localOrder->invoice_id) {
                        // Van számla letöltve, csak küldjük ki
                        $localOrder->sendInvoice();
                    } else if (!$localOrder->draft_invoice_id && !$localOrder->invoice_path && $localOrder->invoice_id) {
                        // RÉGI SZÁMLA, nem generálunk csak letöltünk
                        $path = $bs->downloadInvoice($localOrder->invoice_id, $localOrder, $reseller);
                        if (!$path) {
                            return redirect(action('OrderController@show', ['orderId' => $data['order-id']]))->with([
                                'error' => 'Hiba történt a megrendelés állapotának frissítésekor',
                            ]);
                        }

                        // Elmentjük a számlát helyileg
                        $localOrder->invoice_path = $path;
                        $localOrder->save();
                        $localOrder->sendInvoice();
                    } else if ($localOrder->draft_invoice_id) {
                        // ÚJ típusú számla, először generáltatunk valós számlát
                        // Létrehozzuk az ÉLES számlát
                        $realInvoice = $localOrder->createRealInvoice();
                        $localOrder->invoice_id = $realInvoice->getId();
                        $localOrder->save();
                        $localOrder->refresh();
                        $path = $bs->downloadInvoice($realInvoice->getId(), $localOrder, $reseller);
                        if (!$path) {
                            return redirect(action('OrderController@show', ['orderId' => $data['order-id']]))->with([
                                'error' => 'Hiba történt a megrendelés állapotának frissítésekor',
                            ]);
                        }

                        // Elmentjük a számlát helyileg
                        $localOrder->invoice_path = $path;
                        $localOrder->save();
                        $localOrder->sendInvoice();
                    } else {
                        Log::error('Nincs se régi se új számla azonosító, nem lehet létrehozni számlát automatikusan');
                    }
                } else {
                    $delivery = Delivery::where('order_id', $this->orderService->getLocalOrderByResourceId($orderId)->id);
                    if ($delivery) {
                        $delivery->delete();
                    }
                }

                $successCount++;
            }
        }

        // Mehet a redirect
        if ($successCount == count($orderIds)) {
            return redirect(action('OrderController@index'))->with([
                'success' => sprintf('%s db megrendelés állapota sikeresen frissítve', $successCount),
            ]);
        }

        return redirect(action('OrderController@index'))->with([
            'error' => 'Hiba történt az állapotok frissítésekor',
        ]);
    }
}