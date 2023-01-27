<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Order;
use App\Subesz\BillingoNewService;
use App\Subesz\OrderService;
use App\Subesz\ShoprenterService;
use App\Subesz\StatusService;
use App\Subesz\StockService;
use App\Subesz\WorksheetService;
use App\User;
use Illuminate\Contracts\Foundation\Application;
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

    /** @var StatusService */
    private $statusService;

    /** @var WorksheetService */
    private $worksheetService;

    /** @var string */
    private $creditCardPaidStatus;

    /**
     * OrderController constructor.
     *
     * @param  ShoprenterService          $shoprenterService
     * @param  OrderService               $orderService
     * @param  StockService               $stockService
     * @param  WorksheetService           $worksheetService
     * @param  \App\Subesz\StatusService  $statusService
     */
    public function __construct(
        ShoprenterService $shoprenterService,
        OrderService $orderService,
        StockService $stockService,
        WorksheetService $worksheetService,
        StatusService $statusService
    ) {
        $this->shoprenterApi    = $shoprenterService;
        $this->orderService     = $orderService;
        $this->stockService     = $stockService;
        $this->worksheetService = $worksheetService;
        $this->statusService    = $statusService;

        $this->creditCardPaidStatus = 'b3JkZXJTdGF0dXMtb3JkZXJfc3RhdHVzX2lkPTE4'; // BK. Függőben lévő
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

        if ($order && array_key_exists('order', $order) && property_exists($order['order'], 'error')) {
            return redirect(url()->previous())->with(['error' => 'Shoprenter hiba történt a megrendelés betöltésekor: '.$order['order']->message]);
        }

        // Kezeljük le a státusz frissítéskor létrejövő session-t
        if (! session()->has('updateLocalOrder') || session('updateLocalOrder') != false) {
            $this->orderService->updateLocalOrder($order['order']);
        }

        return view('order.show')->with([
            'order'      => $order,
            'localOrder' => $this->orderService->getLocalOrderByResourceId($order['order']->id),
            'address'    => $this->orderService->getFormattedAddress($order['order']),
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
            'mos-order-ids'      => 'required',
            'order-status-href'  => 'required',
            'mos-payment-method' => 'required_if:order-status-href,b3JkZXJTdGF0dXMtb3JkZXJfc3RhdHVzX2lkPTU=',
        ]);

        // Átalakítjuk a bemenetet
        $orderIds = json_decode($data['mos-order-ids']);

        // Átalakítjuk a státusz linkjét, hogy csak az azonosítót kapjuk vissza
        $statusId = $data['order-status-href'];

        // Végigmegyünk a kijelölésen
        $successCount = 0;
        foreach ($orderIds as $orderId) {
            // Ha Teljesítve a státusz, akkor kérjük el, hogy mi is volt a fizetés módja
            if (array_key_exists('mos-payment-method', $data) && $statusId == 'b3JkZXJTdGF0dXMtb3JkZXJfc3RhdHVzX2lkPTU=') {
                $this->orderService->updatePaymentMethod($orderId, $data['mos-payment-method']);
            }

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
            'order-id'       => 'required',
            'payment-method' => 'required',
            'create-invoice' => 'nullable',
        ]);


        // Frissítjük, a kifizetés módját
        $this->orderService->updatePaymentMethod($data['order-id'], $data['payment-method']);

        // Frissítjük, a számla generálását
        if (!array_key_exists('create-invoice', $data)) {
            $this->orderService->updateCreateInvoice($data['order-id'], false);
        }

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
        $live = env('APP_ENV') == 'production';
        if ($live) {
            $createInvoice = $bs->isBillingoConnected($reseller);
            if (! $createInvoice) {
                Log::info('A viszonteladónak nincs beállítva Billingo API összekötés, ezért nem hozunk létre számlákat.');
            }
        }

        foreach ($orderIds as $orderId) {
            /** @var Order $localOrder */
            $srOrder                 = $this->shoprenterApi->getOrder($orderId);
            $localOrder              = $this->orderService->getLocalOrderByResourceId($orderId);
            $localOrder->reseller_id = $reseller->id;
            $localOrder->save();
            $localOrder->refresh();

            // A megrendeléshez tartozó ügyfelet helyezzük át az új viszonteladóhoz
            $customer = Customer::where('email', $localOrder->email)->first();
            if (! $customer) {
                Log::warning('Nincs ügyfél fiók ilyen email címmel: '.$localOrder->email);
            } else {
                $customer->user_id = $reseller->id;
                $customer->save();
                Log::info('Ügyfél áthelyezve az új viszonteladóhoz.');
            }

            // Ha még nincs lementve számla, akkor generáljunk újat
            if ($live) {
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

    public function downloadInvoice($localOrderId) {
        $order = Auth::user()->orders->find($localOrderId);
        if (Auth::user()->admin) {
            $order = Order::find($localOrderId);
        }

        if (! $order) {
            return redirect(action('OrderController@index'))->with([
                'error' => 'A fiókodhoz nem tartozik ilyen azonosítójú megrendelés.',
            ]);
        }

        $bs = resolve('App\Subesz\BillingoNewService');

        return $bs->downloadInvoiceWithoutSaving($order->invoice_id, $order->reseller);
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

    /**
     * Újra megpróbálja elküldeni a kijelölt megrendelésekhez a számlát, ha még nem ment ki.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function massRegenerateInvoices(Request $request): Redirector|RedirectResponse|Application {
        $data = $request->validate([
            'mri-order-ids' => 'required',
        ]);

        // Átalakítjuk a bemenetet
        $orderResourceIds = json_decode($data['mri-order-ids']);
        $localOrders      = collect();
        $successCount     = 0;
        $errors           = [];
        foreach ($orderResourceIds as $orderResourceId) {
            /** @var Order $localOrder */
            $localOrder = $this->orderService->getLocalOrderByResourceId($orderResourceId);

            if (! $localOrder) {
                return redirect(url()->previous(action('OrderController@index')))->with([
                    'error' => 'Nincs ilyen azonosítójú megrendelés a helyi adatbázisban: '.$orderResourceId,
                ]);
            } else {
                if ($localOrder->reseller_id !== Auth::id() && !Auth::user()->admin) {
                    return redirect(url()->previous(action('OrderController@index')))->with([
                        'error' => sprintf('Egy vagy több megrendelés még folyamatban van, ezért nem lehet újra generálni számlát. (%s %s)', $localOrder->firstname, $localOrder->lastname),
                    ]);
                }
                if (! $localOrder->isCompleted()) {
                    return redirect(url()->previous(action('OrderController@index')))->with([
                        'error' => sprintf('Egy vagy több megrendelés még folyamatban van, ezért nem lehet újra generálni számlát. (%s %s)', $localOrder->firstname, $localOrder->lastname),
                    ]);
                }
            }

            $localOrders->add($localOrder);
        }

        Log::info('Kijelölt megrendelések számlájának újragenerálása...');
        foreach ($localOrders as $localOrder) {
            // Generáljuk le újra a számlákat
            Log::info(sprintf(' - Jelenlegi megrendelés: %s (%s %s)', $localOrder->id, $localOrder->firstname, $localOrder->lastname));
            Log::info(' -- Számla készítése ...');
            $invoiceResponse = $localOrder->createInvoice(false);
            if (! $invoiceResponse['success']) {
                $errors[] = sprintf('%s %s (%s) - %s', $localOrder->firstname, $localOrder->lastname, $localOrder->id, $invoiceResponse['message']);
            } else {
                $successCount++;
            }
            Log::info(' -- ... számlák elintézve.');
        }

        if (count($errors) > 0) {
            array_unshift($errors, ['A következő megrendeléseknél lépett fel probléma:']);

            return redirect(url()->previous(action('OrderController@index')))->withErrors($errors);
        }

        return redirect(url()->previous(action('OrderController@index')))->with([
            'success' => 'A számlák sikeresen el lettek küldve, vagy már el voltak küldve.',
        ]);
    }
}
