<?php

namespace App\Http\Controllers;

use App\Order;
use App\Subesz\BillingoNewService;
use App\Subesz\OrderService;
use App\Subesz\ShoprenterService;
use App\Subesz\StockService;
use App\Subesz\WorksheetService;
use App\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class OrderController extends Controller
{
    /** @var ShoprenterService */
    private $shoprenterApi;

    /** @var OrderService */
    private $orderService;

    /** @var StockService */
    private $stockService;

    /** @var WorksheetService */
    private $worksheetService;

    /**
     * OrderController constructor.
     *
     * @param  ShoprenterService  $shoprenterService
     * @param  OrderService       $orderService
     * @param  StockService       $stockService
     * @param  WorksheetService   $worksheetService
     */
    public function __construct(
        ShoprenterService $shoprenterService,
        OrderService $orderService,
        StockService $stockService,
        WorksheetService $worksheetService
    ) {
        $this->shoprenterApi    = $shoprenterService;
        $this->orderService     = $orderService;
        $this->stockService     = $stockService;
        $this->worksheetService = $worksheetService;
    }

    /**
     * @param  Request  $request
     * @return Factory|View
     */
    public function index(Request $request) {
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
        if ($request->has('filter-region')) {
            $filter['region'] = $request->input('filter-region');
        }
        if ($request->has('filter-delivered')) {
            $filter['delivered'] = $request->input('filter-delivered') == 'true';
        }

        $orders = $this->orderService->getOrdersFiltered($filter);

        $resellers = [];
        foreach (User::all() as $u) {
            if ($u->id == Auth::id()) {
                continue;
            }

            $resellers[] = $u;
        }

        $lastUpdate = [
            'datetime' => $this->orderService->getLastUpdate(),
            'human'    => $this->orderService->getLastUpdateHuman(),
        ];

        return view('order.index')->with([
            'orders'     => $orders,
            'resellers'  => $resellers,
            'statuses'   => $this->shoprenterApi->getStatusesFiltered(),
            'filter'     => $filter,
            'lastUpdate' => $lastUpdate,
        ]);
    }

    /**
     * @param $orderId
     * @return Factory|View
     */
    public function show($orderId) {
        $order = $this->shoprenterApi->getOrder($orderId);

        // Kezeljük le a státusz frissítéskor létrejövő session-t
        if (! session()->has('updateLocalOrder') || session('updateLocalOrder') != false) {
            $this->orderService->updateLocalOrder($order['order']);
        }

        return view('order.show')->with([
            'order'      => $order,
            'localOrder' => $this->orderService->getLocalOrderByResourceId($order['order']->id),
        ]);
    }

    /**
     * @param $orderId
     * @return Factory|View
     */
    public function showStatus($orderId) {
        $order = $this->orderService->getLocalOrderByResourceId($orderId);

        return view('inc.order-status-content')->with([
            'order'    => $order,
            'statuses' => $this->shoprenterApi->getStatusesFiltered(),
        ]);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateStatus(Request $request) {
        $data = $request->validate([
            'order-id'          => 'required',
            'order-status-now'  => 'required',
            'order-status-href' => 'required',
        ]);

        // Ellenőrizzük le, hogy változott-e a státusz href
        $os = resolve('App\Subesz\StatusService')->getOrderStatusByID($data['order-status-href']);
        if ($os->name == $data['order-status-now']) {
            return redirect(url()->previous())->with([
                'error' => 'A megrendelés státusza már a kiválasztott státuszban van',
            ]);
        }

        // Kiszedjük a státusz HREF-et
        $statusId = $data['order-status-href'];

        // Frissítjük az új státuszra
        $result    = $this->orderService->updateStatus($data['order-id'], $statusId);
        $alertType = 'error';
        if ($result['success']) {
            $alertType = 'success';
        }

        return redirect(url()->previous(action('OrderController@show', ['orderId' => $data['order-id']])))->with([
            $alertType => $result['message'],
        ]);
    }

    /**
     * @param  Request  $request
     * @return RedirectResponse|Redirector
     */
    public function massUpdateStatus(Request $request) {
        $data = $request->validate([
            'mos-order-ids'     => 'required',
            'order-status-href' => 'required',
        ]);

        // Átalakítjuk a bemenetet
        $orderIds = json_decode($data['mos-order-ids']);

        // Átalakítjuk a státusz linkjét, hogy csak az azonosítót kapjuk vissza
        $statusId = $data['order-status-href'];

        // Végigmegyünk a kijelölésen
        $successCount = 0;
        foreach ($orderIds as $orderId) {
            // Frissítjük az új státuszra
            $result = $this->orderService->updateStatus($orderId, $statusId);
            if ($result['success']) {
                $successCount++;
            }
        }

        // Mehet a redirect
        if ($successCount == count($orderIds)) {
            return redirect(url()->previous(action('OrderController@index')))->with([
                'success' => sprintf('%s db megrendelés állapota sikeresen frissítve', $successCount),
            ]);
        }

        return redirect(url()->previous(action('OrderController@index')))->with([
            'error' => 'Hiba történt némelyik megrendelés frissítésekor frissítésekor, kérlek nézd át újra a kijelölteket, hogy melyik lett sikertelen',
        ]);
    }

    /**
     * @param  Request  $request
     * @return RedirectResponse|Redirector
     */
    public function completeOrder(Request $request) {
        $data = $request->validate([
            'order-id' => 'required',
        ]);

        // Teljesítve státusz azonosítója
        $statusId  = 'b3JkZXJTdGF0dXMtb3JkZXJfc3RhdHVzX2lkPTU=';
        $result    = $this->orderService->updateStatus($data['order-id'], $statusId);
        $alertType = 'error';
        if ($result['success']) {
            $alertType = 'success';
        }

        return redirect(url()->previous(action('OrderController@show', ['orderId' => $data['order-id']])))->with([
            $alertType => $result['message'],
        ]);
    }

    /**
     * @param  Request  $request
     * @return RedirectResponse|Redirector
     */
    public function massUpdateReseller(Request $request) {
        /** @var BillingoNewService $bs */
        $bs = resolve('App\Subesz\BillingoNewService');

        $data = $request->validate([
            'mur-order-ids'   => 'required',
            'mur-reseller-id' => 'required',
        ]);

        // Átalakítjuk a bemenetet
        $orderIds = json_decode($data['mur-order-ids']);
        $reseller = User::find($data['mur-reseller-id']);
        if (! $reseller) {
            Log::error('Hiba történt a viszonteladó megtalálásakor. Nincs ilyen azonosítójú viszonteladó.');

            return redirect(url()->previous(action('OrderController@index')))->with([
                'error' => 'Hiba történt a viszonteladó megtalálásakor. Nincs ilyen azonosítójú viszonteladó.',
            ]);
        }

        // Végigmegyünk a kijelölésen
        $successCount = 0;
        $failCount    = 0;

        // Kell számlákkal foglalkozni?
        $createInvoice = $bs->isBillingoConnected($reseller);
        if (! $createInvoice) {
            Log::info('A viszonteladónak nincs beállítva Billingo API összekötés, ezért nem hozunk létre számlákat.');
        }

        foreach ($orderIds as $orderId) {
            /** @var Order $localOrder */
            $srOrder                 = $this->shoprenterApi->getOrder($orderId);
            $localOrder              = $this->orderService->getLocalOrderByResourceId($orderId);
            $localOrder->reseller_id = $reseller->id;
            $localOrder->save();
            $localOrder->refresh();

            // Ha még nincs lementve számla, akkor generáljunk újat
            if (! $localOrder->invoice_path && ! $localOrder->invoice_id && $createInvoice) {
                // 1. Partner
                $partner = $bs->createPartner($srOrder, $reseller);
                if (! $partner) {
                    Log::error('Hiba történt a partner létrehozásakor, a számlát nem lehetett létrehozni.');
                    $failCount++;
                    continue;
                }

                // 2. Számla
                $invoice = $bs->createDraftInvoice($srOrder, $partner, $reseller);
                if (! $invoice) {
                    Log::error('Hiba történt a piszkozat számla létrehozásakor.');
                    $failCount++;
                    continue;
                }
                Log::info(sprintf('A piszkozat számla sikeresen létrejött (Azonosító: %s)', $invoice->getId()));

                // 3. Elmentjük a piszkozatot
                $localOrder->draft_invoice_id = $invoice->getId();
                $localOrder->save();
                Log::info(sprintf('A piszkozat számla sikeresen elmentve a megrendeléshez (Megr. Azonosító: %s, Számla azonosító: %s)', $localOrder->id, $invoice->getId()));
            }

            $successCount++;
        }

        $returnType = $successCount == 0 ? 'error' : 'success';
        $output     = sprintf('%s db megrendelés sikeresen frissítve', $successCount);
        if ($failCount > 0) {
            $output .= sprintf(', %s db megrendeléshez a számla nem jött létre sikeresen.', $failCount);
        } else {
            $output .= '.';
        }

        return redirect(url()->previous(action('OrderController@index')))->with([
            $returnType => $output,
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateOrderIncomes() {
        set_time_limit(0);
        foreach (Order::all() as $order) {
            $order->updateIncome();
        }

        return redirect(action('RevenueController@hqFinance'))->with([
            'success' => 'Bevételek sikeresen frissítve',
        ]);
    }

    /***
     * @param  Request  $request
     * @param           $orderID
     * @return array|string
     * Bejövő request (orderID) alapján renderel egy templatet, ami a megrendelésen lévő megjegyzéseket listázza.
     * Visszatérési értéke HTML/Text
     */
    public function getCommentsHTML(Request $request, $orderID) {
        $result = $this->orderService->getCommentsHTML((int) $orderID);
        if ($result['success']) {
            return view("inc.render-order-comments")->with(['order' => $result['order']])->toHtml();
        } else {
            return $result['message'];
        }
    }
}
