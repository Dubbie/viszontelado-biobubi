<?php

namespace App\Http\Controllers;

use App\Mail\NewOrder;
use App\Order;
use App\OrderProducts;
use App\Product;
use App\Subesz\BillingoNewService;
use App\Subesz\BillingoService;
use App\Subesz\OrderService;
use App\Subesz\ShoprenterService;
use App\Subesz\StockService;
use App\User;
use App\UserZip;
use Auth;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mail;
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
     *
     * @param  OrderService        $orderService
     * @param  ShoprenterService   $shoprenterService
     * @param  BillingoNewService  $billingoNewService
     */
    public function __construct(
        OrderService $orderService,
        ShoprenterService $shoprenterService,
        BillingoNewService $billingoNewService
    ) {
        $this->orderService       = $orderService;
        $this->shoprenterApi      = $shoprenterService;
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
        $osds  = $this->shoprenterApi->getAllStatuses();
        if (! $osds || ! property_exists($osds, 'items')) {
            Log::error('A Shoprenter API nem tért vissza eredményekkel');

            return redirect(action('UserController@home'))->with([
                'error' => 'Hiba történt a Shoprenter API-hoz való kapcsolódáskor. Próbáld újra később.',
            ]);
        }

        $statusMap = [];
        foreach ($osds->items as $osd) {
            $orderStatusId = str_replace(sprintf('%s/orderStatuses/', env('SHOPRENTER_API')), '', $osd->orderStatus->href);

            $statusMap[$orderStatusId] = [
                'name'  => $osd->name,
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

        $successCount   = 0;
        $orderResources = [];
        foreach ($orders as $order) {
            $orderResources[] = $order->id;

            $muted = true;
            if (env('APP_ENV') != 'local') {
                if ($local = $this->orderService->updateLocalOrder($order, $muted)) {
                    $successCount++;
                }
            } else {
                if (! $this->orderService->getLocalOrderByResourceId($order->id)) {
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
     * @param  string   $privateKey
     * @param  Request  $request
     * @return array
     */
    public function handleWebhook(string $privateKey, Request $request)
    {
        Log::info('- Shoprenter Új Megrendelés Webhook -');

        // Ellenőrizzük a kulcsot
        if (env('PRIVATE_KEY') != $privateKey) {
            return ['error' => 'Hibás privát kulcs lett megadva'];
        }

        /** @var StockService $ss */
        $ss    = resolve('App\Subesz\StockService');
        $array = json_decode($request->input('data'), true);
        Log::info(sprintf('-- Megrendelések száma: %s db', count($array['orders']['order'])));
        foreach ($array['orders']['order'] as $_order) {
            Log::info($_order);
            $orderId = str_replace('orders/', '', $_order['innerResourceId']);

            // Elmentése a Megrendelésnek db-be
            $localOrder                       = new Order();
            $localOrder->shipping_postcode    = $_order['shippingPostcode'];
            $localOrder->shipping_city        = $_order['shippingCity'];
            $localOrder->shipping_address     = sprintf('%s %s', $_order['shippingAddress1'], $_order['shippingAddress2']);
            $localOrder->inner_id             = $_order['innerId'];
            $localOrder->inner_resource_id    = $orderId;
            $localOrder->total                = $_order['total'];
            $localOrder->total_gross          = $_order['totalGross'];
            $localOrder->tax_price            = $_order['taxPrice'];
            $localOrder->firstname            = $_order['firstname'];
            $localOrder->lastname             = $_order['lastname'];
            $localOrder->email                = $_order['email'];
            $localOrder->phone                = $_order['phone'];
            $localOrder->shipping_method_name = $_order['shippingMethodName'];
            $localOrder->payment_method_name  = $_order['paymentMethodName'];
            $localOrder->status_text          = $_order['orderHistory']['statusText'];
            $localOrder->status_color         = '#ff00ff';
            $localOrder->created_at           = date('Y-m-d H:i:s');

            if (! $localOrder->save()) {
                return ['success' => false];
            }

            $order = $this->shoprenterApi->getOrder($orderId);

            // Elmentjük a készlethez szükséges dolgokat
            $orderedProducts = $this->orderService->getOrderedProductsFromOrder($order);
            $booked          = $ss->bookOrder($orderedProducts, $localOrder->id);
            if ($booked) {
                $this->orderService->saveOrderedProducts($orderedProducts, $localOrder->id);
            }

            // Mentsük el a számlát
            $reseller = $localOrder->getReseller()['correct'];
            Log::info('Hozzátartozó számlázó fiók neve: '.$reseller->name);

            // Elküldjük róla a levelet is
            if ($reseller->email != 'hello@semmiszemet.hu') {
                Mail::to($reseller)->send(new NewOrder($order, $reseller));
                Log::info('Levél elküldve az alábbi e-mail címre: '.$reseller->email);
            }

            // Ha nincs billing összekötés ne hozzunk létre semmit
            if (! $this->billingoNewService->isBillingoConnected($reseller)) {
                Log::info('A viszonteladónak nincs beállítva billingo összekötés, ezért nem hozunk létre számlát.');

                return ['success' => true];
            }

            // 1. Partner
            $partner = $this->billingoNewService->createPartner($order, $reseller);
            if (! $partner) {
                Log::error('Hiba történt a partner létrehozásakor, a számlát nem lehet létrehozni.');

                return ['success' => false];
            }

            // 2. Számla
            $invoice = $this->billingoNewService->createDraftInvoice($order, $partner, $reseller);
            if (! $invoice) {
                Log::error('Hiba történt a számla létrehozásakor.');

                return ['success' => false];
            }
            Log::info(sprintf('A piszkozat számla sikeresen létrejött (Azonosító: %s)', $invoice->getId()));

            // 3. Elmentjük a piszkozatot
            $localOrder->draft_invoice_id = $invoice->getId();
            $localOrder->save();
            Log::info(sprintf('A piszkozat számla sikeresen elmentve a megrendeléshez (Megr. Azonosító: %s, Számla azonosító: %s)', $localOrder->id, $invoice->getId()));

            // Trackeljük Klaviyo-ba
            $ks = resolve('App\Subesz\KlaviyoService');
            $ks->trackOrder($order);
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

    /**
     *
     */
    public function testShoprenter()
    {
        
    }

    /**
     * Teszteli, hogy okos-e a billingo
     *
     * @return bool
     */
    public function testBillingo(): bool
    {
        $config = Configuration::getDefaultConfiguration()->setApiKey('X-API-KEY', '');

        $docu = new DocumentApi(new Client(), $config);
        try {
            dd($docu->listDocument());
        } catch (ApiException $e) {
            dd($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @return array
     */
    public function getProduct(Request $request): array
    {
        $product        = $this->shoprenterApi->getProduct($request->input('sku'));
        $klaviyoProduct = [
            "ProductName"    => $product->productDescriptions[0]->name,
            "ProductID"      => $product->innerId,
            "ImageURL"       => $product->allImages->mainImage,
            "URL"            => 'https://biobubi.hu/'.$product->urlAliases[0]->urlAlias,
            "Brand"          => $product->manufacturer->name ?? 'Semmiszemét',
            "Price"          => $product->price * 1.27,
            "CompareAtPrice" => $product->price * 1.27,
        ];

        header('Access-Control-Allow-Origin: https://biobubi.hu');

        return $klaviyoProduct;
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkZip(Request $request)
    {
        Log::info('Irányítószám ellenőrzése...');
        $uzip     = UserZip::whereZip($request->get('zip'))->first();
        $reseller = $uzip ? $uzip->user : null;

        if ($reseller) {
            Log::info('Az irányítószám megtalálva, viszonteladó: '.$reseller->name);

            return response()->json([
                'success' => true,
                'found'   => true,
                'message' => 'Irányítószám megtalálva.',
            ]);
        } else {
            Log::info('Az irányítószám nincs a rendszerben.');

            return response()->json([
                'success' => true,
                'found'   => false,
                'message' => 'Irányítószám nincs megtalálva.',
            ]);
        }
    }
}
