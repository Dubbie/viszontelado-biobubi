<?php

namespace App\Http\Controllers;

use App\Mail\NewOrder;
use App\Order;
use App\Product;
use App\Subesz\BillingoNewService;
use App\Subesz\BillingoService;
use App\Subesz\OrderService;
use App\Subesz\ShoprenterService;
use App\Subesz\StockService;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Swagger\Client\Api\BankAccountApi;
use Swagger\Client\Api\DocumentApi;
use Swagger\Client\ApiException;
use Swagger\Client\Configuration;

class ShoprenterController extends Controller
{
    /** @var ShoprenterService */
    private $shoprenterApi;

    /** @var OrderService */
    private $orderService;

    /** @var BillingoNewService */
    private $billingoNewService;

    /**
     * ShoprenterController constructor.
     * @param OrderService $orderService
     * @param ShoprenterService $shoprenterService
     * @param BillingoNewService $billingoNewService
     */
    public function __construct(OrderService $orderService, ShoprenterService $shoprenterService, BillingoNewService $billingoNewService)
    {
        $this->orderService = $orderService;
        $this->shoprenterApi = $shoprenterService;
        $this->billingoNewService = $billingoNewService;
    }

    /**
     * @param $privateKey
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateOrders($privateKey)
    {
        // Ellenőrizzük a kulcsot
        if (env('PRIVATE_KEY') != $privateKey) {
            Log::error('-- Hiba a Shoprenterből való frissítéskor, nem egyezett a privát kulcs --');
            return redirect(action('OrderController@index'))->with([
                'error' => 'Hibás privát kulcs lett megadva',
            ]);
        }

        Log::info('-- Shoprenter API-ból frissítés megkezdése --');
        $start = Carbon::now();
        $osds = $this->shoprenterApi->getAllStatuses();

        $statusMap = [];
        foreach ($osds->items as $osd) {
            $orderStatusId = str_replace(sprintf('%s/orderStatuses/', env('SHOPRENTER_API')), '', $osd->orderStatus->href);

            $statusMap[$orderStatusId] = [
                'name' => $osd->name,
                'color' => $osd->color,
            ];
        }

        $orders = $this->shoprenterApi->getBatchedOrders();
        if (count($orders) == 0) {
            Log::info('- Nem voltak megrendelések a visszatérési értékben -');
            return redirect(action('OrderController@index'))->with([
                'error' => 'Hiba történt a megrendelések frissítésekor',
            ]);
        }

        $successCount = 0;
        $orderResources = [];
        foreach ($orders as $order) {
            $orderResources[] = $order->id;

            $muted = true;
            if (env('APP_ENV') != 'local') {
                if ($local = $this->orderService->updateLocalOrder($order, $muted)) {
                    $successCount++;
                }
            } else {
                if (!$this->orderService->getLocalOrderByResourceId($order->id)) {
                    if ($local = $this->orderService->updateLocalOrder($order, $muted)) {
                        $successCount++;
                    }
                }
            }
        }

        // Töröljük ki azokat amik már nincsenek a rendszerbe
        $notFound = Order::whereNotIn('inner_resource_id', $orderResources)->where('created_at', '<', $start)->get();
        if (count($notFound) > 0) {
            Log::debug('- Nem talált inner resource ID: -');
            /** @var Order $order */
            foreach ($notFound as $order) {
                Log::debug($order->inner_resource_id);
            }
            Log::debug('- Nem talált inner resource ID vége -');
        }
        Order::whereNotIn('inner_resource_id', $orderResources)->where('created_at', '<', $start)->delete();

        if ($successCount == count($orders)) {
            $elapsed = $start->floatDiffInSeconds();
            Log::info(sprintf('--- %s db megrendelés sikeresen frissítve (Eltelt idő: %ss)', $successCount, $elapsed));
            Log::info('-- Shoprenter API-ból frissítés vége --');
            return redirect(action('OrderController@index'))->with([
                'success' => sprintf('%s db megrendelés sikeresen frissítve (Eltelt idő: %ss)', $successCount, $elapsed),
            ]);
        } else {
            return redirect(action('OrderController@index'))->with([
                'error' => 'Hiba történt a megrendelések frissítésekor',
            ]);
        }
    }

    /**
     * @param $privateKey
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

        /** @var StockService $ss */
        $ss = resolve('App\Subesz\StockService');
        $array = json_decode($request->input('data'), true);
        Log::info(sprintf('-- Megrendelések száma: %s db', count($array['orders']['order'])));
        foreach ($array['orders']['order'] as $_order) {
            Log::info($_order);
            $orderId = str_replace('orders/', '', $_order['innerResourceId']);

            // Elmentése a Megrendelésnek db-be
            $localOrder = new Order();
            $localOrder->shipping_postcode = $_order['shippingPostcode'];
            $localOrder->shipping_city = $_order['shippingCity'];
            $localOrder->shipping_address = sprintf('%s %s', $_order['shippingAddress1'], $_order['shippingAddress2']);
            $localOrder->inner_id = $_order['innerId'];
            $localOrder->inner_resource_id = $orderId;
            $localOrder->total = $_order['total'];
            $localOrder->total_gross = $_order['totalGross'];
            $localOrder->tax_price = $_order['taxPrice'];
            $localOrder->firstname = $_order['firstname'];
            $localOrder->lastname = $_order['lastname'];
            $localOrder->email = $_order['email'];
            $localOrder->shipping_method_name = $_order['shippingMethodName'];
            $localOrder->payment_method_name = $_order['paymentMethodName'];
            $localOrder->status_text = $_order['orderHistory']['statusText'];
            $localOrder->status_color = '#ff00ff';
            $localOrder->created_at = date('Y-m-d H:i:s');

            if (!$localOrder->save()) {
                return ['success' => false];
            }

            $order = $this->shoprenterApi->getOrder($orderId);

            // Elmentjük a készlethez szükséges dolgokat
            $orderedProducts = $this->orderService->getOrderedProductsFromOrder($order);
            $ss->bookOrder($orderedProducts, $localOrder->id);
            $this->orderService->saveOrderedProducts($orderedProducts, $localOrder->id);

            // Mentsük el a számlát
            /** @var Order $localOrder */
            $reseller = $localOrder->getReseller()['correct'];
            Log::info('Hozzátartozó számlázó fiók neve: ' . $reseller->name);

            // Elküldjük róla a levelet is
            if ($reseller->email != 'hello@semmiszemet.hu') {
                \Mail::to($reseller)->send(new NewOrder($order, $reseller));
                Log::info('Levél elküldve az alábbi e-mail címre: ' . $reseller->email);
            }

            // Ha nincs billing összekötés ne hozzunk létre semmit
            if (!$this->billingoNewService->isBillingoConnected($reseller)) {
                Log::info('A viszonteladónak nincs beállítva billingo összekötés, ezért nem hozunk létre számlát.');
                return ['success' => true];
            }

            // 1. Partner
            $partner = $this->billingoNewService->createPartner($order, $reseller);
            if (!$partner) {
                Log::error('Hiba történt a partner létrehozásakor, a számlát nem lehet létrehozni.');
                return ['success' => false];
            }

            // 2. Számla
            $invoice = $this->billingoNewService->createDraftInvoice($order, $partner, $reseller);
            if (!$invoice) {
                Log::error('Hiba történt a számla létrehozásakor.');
                return ['success' => false];
            }
            Log::info(sprintf('A piszkozat számla sikeresen létrejött (Azonosító: %s)', $invoice->getId()));

            // 3. Elmentjük a piszkozatot
            $localOrder->draft_invoice_id = $invoice->getId();
            $localOrder->save();
            Log::info(sprintf('A piszkozat számla sikeresen elmentve a megrendeléshez (Megr. Azonosító: %s, Számla azonosító: %s)', $localOrder->id, $invoice->getId()));
        }

        return ['success' => true];
    }

    /**
     *
     */
    public function updateProducts()
    {
        $this->shoprenterApi->updateProducts();
        Log::info('Termékek sikeresen frissítve a Shoprenter adatbázisából!');
    }

    public function testShoprenter()
    {
        $_order = array(
            'storeName' => 'biobubi',
            'innerId' => '2326',
            'innerResourceId' => 'orders/b3JkZXItb3JkZXJfaWQ9MjMyNg==',
            'outerResourceId' => '',
            'firstname' => 'dr. Feczkó',
            'lastname' => 'Erika',
            'phone' => '+36708821353',
            'fax' => '',
            'email' => 'feczkoeri@gmail.com',
            'cart_token' => 'cart',
            'shippingFirstname' => 'dr. Feczkó',
            'shippingLastname' => 'Erika',
            'shippingCompany' => '',
            'shippingAddress1' => 'Újosztás 25.',
            'shippingAddress2' => '',
            'shippingCity' => 'Táborfalva',
            'shippingCountryName' => 'Magyarország',
            'shippingZoneName' => '',
            'shippingPostcode' => '2440',
            'paymentFirstname' => 'dr. Feczkó',
            'paymentLastname' => 'Erika',
            'paymentCompany' => '',
            'paymentAddress1' => 'Újosztás 25.',
            'paymentAddress2' => '',
            'paymentCity' => 'Táborfalva',
            'paymentCountryName' => 'Magyarország',
            'paymentZoneName' => 'Pest',
            'shippingMethodName' => 'Házhoz szállítás Semmi Szemét futárral',
            'shippingNetPrice' => 0,
            'shippingGrossPrice' => '0',
            'shippingInnerResourceId' => 'shippingModeExtend/c2hpcHBpbmdNb2RlLWlkPTE4',
            'paymentMethodName' => 'Utánvétel',
            'paymentNetPrice' => 0,
            'paymentGrossPrice' => 0,
            'couponCode' => '',
            'couponGrossPrice' => NULL,
            'languageId' => '1',
            'languageCode' => 'hu',
            'comment' => '',
            'total' => '1252',
            'totalGross' => '1590',
            'taxPrice' => '338',
            'currency' => 'HUF',
            'paymentPostcode' => '2381',
            'paymentTaxnumber' => '',
            'orderHistory' =>
                array(
                    'status' => '1',
                    'statusText' => 'Függőben lévő',
                    'comment' => '',
                ),
            'orderProducts' =>
                array(
                    'orderProduct' =>
                        array(
                            0 =>
                                array(
                                    'innerId' => '8467',
                                    'innerResourceId' => 'orderProducts/b3JkZXJQcm9kdWN0LW9yZGVyX3Byb2R1Y3RfaWQ9ODQ2Nw==',
                                    'outerResourceId' => '',
                                    'name' => 'BioBubi mosószer próbacsomag',
                                    'sku' => '1',
                                    'price' => '1251.969',
                                    'currency' => 'HUF',
                                    'taxRate' => '27.0000',
                                    'quantity' => '1',
                                    'image' => 'https://biobubi.hu/custom/biobubi/image/data/Mososzer_probacsomag.png',
                                    'category' => 'BioBubi Mosószer, Próbáld ki a betétdíjas mosószert',
                                    'volume' =>
                                        array(
                                            'height' => '0.00',
                                            'width' => '0.00',
                                            'length' => '0.00',
                                            'volumeUnit' =>
                                                array(
                                                    0 =>
                                                        array(
                                                            'unit' => 'cm',
                                                            'language' => 'hu',
                                                        ),
                                                ),
                                        ),
                                    'weight' =>
                                        array(
                                            'weight' => '0.00',
                                            'weightUnit' =>
                                                array(
                                                    0 =>
                                                        array(
                                                            'unit' => 'kg',
                                                            'language' => 'hu',
                                                        ),
                                                ),
                                        ),
                                ),
                        ),
                ),
        );

        /** @var StockService $ss */
        /** @var Order $localOrder */
        $ss = resolve('App\Subesz\StockService');
        $orderId = 'b3JkZXItb3JkZXJfaWQ9MjMzMg==';
//        $orderId = 'b3JkZXItb3JkZXJfaWQ9MjM1Nw==';
        $order = $this->shoprenterApi->getOrder($orderId);
        $orderedProducts = $this->orderService->getOrderedProductsFromOrder($order);
        $localOrder = $this->orderService->getLocalOrderByResourceId($orderId);
        $ss->bookOrder($orderedProducts, $localOrder->id);
//        $this->orderService->saveOrderedProducts($orderedProducts, $localOrder->id);
//        $localOrder->delete();
//        dd($ss->subtractStockFromOrder($localOrder->id));
    }

    /**
     * Teszteli, hogy okos-e a billingo
     *
     * @return bool
     */
    public function testBillingo()
    {
    }
}
