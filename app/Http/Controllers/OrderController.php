<?php

namespace App\Http\Controllers;

use App\Delivery;
use App\Mail\RegularOrderCompleted;
use App\Mail\TrialOrderCompleted;
use App\Order;
use App\Subesz\BillingoNewService;
use App\Subesz\BillingoService;
use App\Subesz\KlaviyoService;
use App\Subesz\OrderService;
use App\Subesz\ShoprenterService;
use App\Subesz\StockService;
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

    /** @var StockService */
    private $stockService;

    /**
     * OrderController constructor.
     * @param ShoprenterService $shoprenterService
     * @param OrderService $orderService
     * @param StockService $stockService
     */
    public function __construct(ShoprenterService $shoprenterService, OrderService $orderService, StockService $stockService)
    {
        $this->shoprenterApi = $shoprenterService;
        $this->orderService = $orderService;
        $this->stockService = $stockService;
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

            $resellers[] = $u;
        }

        $lastUpdate = [
            'datetime' => $this->orderService->getLastUpdate(),
            'human' => $this->orderService->getLastUpdateHuman(),
        ];

        return view('order.index')->with([
            'orders' => $orders,
            'resellers' => $resellers,
            'statuses' => $this->shoprenterApi->getStatusesFiltered(),
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
            'localOrder' => $this->orderService->getLocalOrderByResourceId($order['order']->id),
        ]);
    }

    /**
     * @param $orderId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showStatus($orderId)
    {
        $order = $this->shoprenterApi->getOrder($orderId);

        return view('inc.order-status-content')->with([
            'order' => $order,
            'statuses' => $this->shoprenterApi->getStatusesFiltered(),
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
        /** @var KlaviyoService $ks */
        $ks = resolve('App\Subesz\KlaviyoService');

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
                $order = $localOrder->getShoprenterOrder();
                $ks->fulfillOrder($order);

                Log::info(sprintf('Megrendelés teljesítve (Azonosító: %s)', $localOrder->id));

                /** @var User $reseller */
                $reseller = $localOrder->getReseller()['correct'];

                // Levonjuk a készletet
                $this->stockService->subtractStockFromOrder($localOrder->id);

                if (!$bs->isBillingoConnected($reseller)) {
                    Log::info('A felhasználónak nincs billingo összekötése, ezért nem készül számla.');
                } else {
                    // Csak az új típusú számlázást támogatjuk mostantól, és csak akkor hozzuk létre, ha nincs még számla
                    if ($localOrder->draft_invoice_id && (!$localOrder->invoice_path && !$localOrder->invoice_id)) {
                        // 1. Létrehozzuk az éles számlát, ha sikerül
                        $realInvoice = $localOrder->createRealInvoice();
                        if (!$realInvoice) {
                            Log::error(sprintf('Nem sikerült létrehozni valódi számlát. (Piszkozat: %s, Megr. Azonosító: %s)', $localOrder->draft_invoice_id, $localOrder->id));
                            return redirect(action('OrderController@show', ['orderId' => $data['order-id']]))->with([
                                'error' => 'Hiba történt a piszkozat számla átalakításakor',
                            ]);
                        } else {
                            // Jók vagyunk
                            $localOrder->invoice_id = $realInvoice->getId();
                            $localOrder->save();
                            $localOrder->refresh();
                            $path = $bs->downloadInvoice($realInvoice->getId(), $localOrder, $reseller);
                            if (!$path) {
                                Log::error('Hiba történt a megrendelés állapotának frissítésekor');
                                return redirect(action('OrderController@show', ['orderId' => $data['order-id']]))->with([
                                    'error' => 'Hiba történt a megrendelés állapotának frissítésekor',
                                ]);
                            }

                            // Elmentjük a számlát helyileg
                            $localOrder->invoice_path = $path;
                            $localOrder->save();
                            $localOrder->sendInvoice();
                        }
                    } else if ($localOrder->draft_invoice_id && $localOrder->invoice_id && $localOrder->invoice_path) {
                        Log::info(sprintf('A megrendeléshez már létrejött számla ezért nem hozunk létre újabbat. (Megr. Azonosító: %s)', $localOrder->id));
                    } else {
                        Log::error('Nincs se régi se új számla azonosító, nem lehet létrehozni számlát automatikusan (Régi megrendelés)');
                    }
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
        /** @var KlaviyoService $ks */
        $ks = resolve('App\Subesz\KlaviyoService');

        $data = $request->validate([
            'mos-order-ids' => 'required',
            'order-status-href' => 'required',
        ]);

        // Átalakítjuk a bemenetet
        $orderIds = json_decode($data['mos-order-ids']);

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
                    $order = $localOrder->getShoprenterOrder();
                    $ks->fulfillOrder($order);

                    // A készletet lerendezzük
                    $this->stockService->subtractStockFromOrder($localOrder->id);

                    if (!$bs->isBillingoConnected($reseller)) {
                        Log::info('A felhasználónak nincs billingo összekötése, ezért nem készül számla.');
                    } else {
                        // Csak az új típusú számlázást támogatjuk mostantól, és csak akkor hozzuk létre, ha nincs még számla
                        if ($localOrder->draft_invoice_id && (!$localOrder->invoice_path && !$localOrder->invoice_id)) {
                            // 1. Létrehozzuk az éles számlát, ha sikerül
                            $realInvoice = $localOrder->createRealInvoice();
                            if (!$realInvoice) {
                                Log::error(sprintf('Nem sikerült létrehozni valódi számlát. (Piszkozat: %s, Megr. Azonosító: %s)', $localOrder->draft_invoice_id, $localOrder->id));
                                return redirect(action('OrderController@show', ['orderId' => $orderId]))->with([
                                    'error' => 'Hiba történt a piszkozat számla átalakításakor',
                                ]);
                            } else {
                                // Jók vagyunk
                                $localOrder->invoice_id = $realInvoice->getId();
                                $localOrder->save();
                                $localOrder->refresh();
                                $path = $bs->downloadInvoice($realInvoice->getId(), $localOrder, $reseller);
                                if (!$path) {
                                    Log::error('Hiba történt a megrendelés állapotának frissítésekor');
                                    return redirect(action('OrderController@show', ['orderId' => $orderId]))->with([
                                        'error' => 'Hiba történt a megrendelés állapotának frissítésekor',
                                    ]);
                                }

                                // Elmentjük a számlát helyileg
                                $localOrder->invoice_path = $path;
                                $localOrder->save();
                                $localOrder->sendInvoice();
                            }
                        } else if ($localOrder->draft_invoice_id && $localOrder->invoice_id && $localOrder->invoice_path) {
                            Log::info(sprintf('A megrendeléshez már létrejött számla ezért nem hozunk létre újabbat. (Megr. Azonosító: %s)', $localOrder->id));
                        } else {
                            Log::error('Nincs se régi se új számla azonosító, nem lehet létrehozni számlát automatikusan (Régi megrendelés)');
                        }
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function massUpdateReseller(Request $request)
    {
        /** @var BillingoNewService $bs */
        $bs = resolve('App\Subesz\BillingoNewService');

        $data = $request->validate([
            'mur-order-ids' => 'required',
            'mur-reseller-id' => 'required',
        ]);

        // Átalakítjuk a bemenetet
        $orderIds = json_decode($data['mur-order-ids']);
        $reseller = User::find($data['mur-reseller-id']);
        if (!$reseller) {
            Log::error('Hiba történt a viszonteladó megtalálásakor. Nincs ilyen azonosítójú viszonteladó.');
            return redirect(url()->previous(action('OrderController@index')))->with([
                'error' => 'Hiba történt a viszonteladó megtalálásakor. Nincs ilyen azonosítójú viszonteladó.',
            ]);
        }

        // Végigmegyünk a kijelölésen
        $successCount = 0;
        $failCount = 0;

        // Kell számlákkal foglalkozni?
        $createInvoice = $bs->isBillingoConnected($reseller);
        if (!$createInvoice) {
            Log::info('A viszonteladónak nincs beállítva Billingo API összekötés, ezért nem hozunk létre számlákat.');
        }

        foreach ($orderIds as $orderId) {
            /** @var Order $localOrder */
            $srOrder = $this->shoprenterApi->getOrder($orderId);
            $localOrder = $this->orderService->getLocalOrderByResourceId($orderId);
            $localOrder->reseller_id = $reseller->id;
            $localOrder->save();
            $localOrder->refresh();

            // Ha még nincs lementve számla, akkor generáljunk újat
            if (!$localOrder->invoice_path && !$localOrder->invoice_id && $createInvoice) {
                // 1. Partner
                $partner = $bs->createPartner($srOrder, $reseller);
                if (!$partner) {
                    Log::error('Hiba történt a partner létrehozásakor, a számlát nem lehetett létrehozni.');
                    $failCount++;
                    continue;
                }

                // 2. Számla
                $invoice = $bs->createDraftInvoice($srOrder, $partner, $reseller);
                if (!$invoice) {
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
        $output = sprintf('%s db megrendelés sikeresen frissítve', $successCount);
        if ($failCount > 0) {
            $output .= sprintf(', %s db megrendeléshez a számla nem jött létre sikeresen.', $failCount);
        } else {
            $output .= '.';
        }

        return redirect(url()->previous(action('OrderController@index')))->with([
            $returnType => $output,
        ]);
    }
}