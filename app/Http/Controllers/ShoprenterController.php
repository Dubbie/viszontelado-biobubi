<?php

namespace App\Http\Controllers;

use App\Mail\NewOrder;
use App\Order;
use App\RegionZip;
use App\Subesz\BillingoNewService;
use App\Subesz\CustomerService;
use App\Subesz\OrderService;
use App\Subesz\ShoprenterService;
use App\Subesz\StatusService;
use App\Subesz\StockService;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mail;

class ShoprenterController extends Controller
{
    /** @var ShoprenterService */
    private $shoprenterApi;

    /** @var OrderService */
    private $orderService;

    /** @var BillingoNewService */
    private $billingoNewService;

    /** @var \App\Subesz\StatusService */
    private $statusService;

    /** @var \App\Subesz\CustomerService */
    private $customerService;

    /**
     * ShoprenterController constructor.
     *
     * @param  OrderService               $orderService
     * @param  ShoprenterService          $shoprenterService
     * @param  BillingoNewService         $billingoNewService
     * @param  \App\Subesz\StatusService  $statusService
     */
    public function __construct(
        OrderService $orderService,
        ShoprenterService $shoprenterService,
        BillingoNewService $billingoNewService,
        StatusService $statusService,
        CustomerService $customerService
    ) {
        $this->orderService       = $orderService;
        $this->shoprenterApi      = $shoprenterService;
        $this->billingoNewService = $billingoNewService;
        $this->statusService      = $statusService;
        $this->customerService    = $customerService;
    }

    /**
     * @param $privateKey
     * @return bool
     */
    public function updateOrders($privateKey) {
        set_time_limit(0);

        // Ellenőrizzük a kulcsot
        if (env('PRIVATE_KEY') != $privateKey) {
            Log::error('-- Hiba a Shoprenterből való frissítéskor, nem egyezett a privát kulcs --');

            return false;
        }

        Log::info('-- Shoprenter API-ból frissítés megkezdése --');
        $start = Carbon::now();
        $osds  = $this->shoprenterApi->getAllStatuses();
        if (! $osds || ! property_exists($osds, 'items')) {
            Log::error('A Shoprenter API nem tért vissza eredményekkel');

            return false;
        }

        $orders = $this->shoprenterApi->getBatchedOrders();
        if (count($orders) == 0) {
            Log::info('- Nem voltak megrendelések a visszatérési értékben -');

            return false;
        }

        Log::debug('Összesen '.count($orders).'db megrendelés érkezett a Shoprenterből.');
        $successCount       = 0;
        $ordersByResourceId = [];
        $muted              = false;
        foreach ($orders as $order) {
            $ordersByResourceId[$order->id] = $order;
        }

        // 1. Töröljük azokat amiket a Shoprenter nem ad vissza, de helyileg még meg van.
        $notFound         = Order::whereNotIn('inner_resource_id', array_keys($ordersByResourceId))->where('created_at', '<', $start)->get();
        $startOrdersCount = Order::count();
        if (count($notFound) > 0) {
            Log::debug('- Az alábbi megrendelések nem szerepelnek már a ShopRenter-ben: -');
            /** @var Order $order */
            foreach ($notFound as $order) {
                Log::debug(sprintf('Belső ID: %s', $order->inner_resource_id));
                Log::debug(sprintf('Helyi ID: %s', $order->id));
                Log::debug(sprintf('Megrendelő: %s %s', $order->firstname, $order->lastname));
                Log::debug(sprintf('Állapot: %s', $order->status_text));
                Log::debug(sprintf("Létrehozva: %s\r\n", $order->created_at->format('Y.m.d H:i:s')));
                $successCount++;
            }
            Order::whereNotIn('inner_resource_id', array_keys($ordersByResourceId))->where('created_at', '<', $start)->delete();
            Log::debug('- A megrendelések törölve lettek. -');
            if (Order::count() >= $startOrdersCount) {
                Log::warning('NEM KERÜLTEK TÖRLÉSRE A MEGRENDELÉSEK HALÓ???');
            }
        } else {
            Log::debug('- Nincs olyan megrendelés amit törölni kéne -');
        }

        // Létrehozzuk az újakat, amik szerepelnek ShopRenter-ben, de nálunk viszont még nem.
        //foreach ($ordersByResourceId as $shoprenterResourceId => $srOrder) {
        //    if (! $this->orderService->getLocalOrderByResourceId($shoprenterResourceId)) {
        //        if ($local = $this->orderService->updateLocalOrder($srOrder, $muted)) {
        //            $successCount++;
        //        }
        //    }
        //}

        $elapsed = $start->floatDiffInSeconds();
        Log::info(sprintf('--- %s db megrendelés sikeresen frissítve (Eltelt idő: %ss)', $successCount, $elapsed));
        Log::info('-- Shoprenter API-ból frissítés vége --');

        return true;
    }

    /**
     * @param  string   $privateKey
     * @param  Request  $request
     * @return array
     */
    public function handleWebhook(string $privateKey, Request $request) {
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

            // Ellenőrizzük le, hogy nincs-e már ilyen megrendelés, ne duplikájl!
            if ($this->orderService->getLocalOrderByResourceId($orderId)) {
                Log::warning('Már létezik ilyen megrendelés, duplikált webhook érkezett be.');

                return ['success' => false];
            }

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
            $localOrder->status_color         = $this->statusService->getColorByStatusName($_order['orderHistory']['statusText']);
            $localOrder->created_at           = date('Y-m-d H:i:s');

            // Eldöntjük, hogy kapjon-e online fizetéses végső fizetés típust
            if ($localOrder->payment_method_name == 'Online bankkártyás fizetés') {
                $localOrder->final_payment_method = 'Online Bankkártya';
            }

            if (! $localOrder->save()) {
                return ['success' => false];
            }

            $order = $this->shoprenterApi->getOrder($orderId);

            // Elmentjük a készlethez szükséges dolgokat
            // TODO: Újra implementáljuk a készletes dolgokat
            $orderedProducts = $this->orderService->getOrderedProductsFromOrder($order);
            //$booked          = $ss->bookOrder($orderedProducts, $localOrder->id);
            //if ($booked) {
            //    $this->orderService->saveOrderedProducts($orderedProducts, $localOrder->id);
            //}

            try {
                $this->orderService->saveOrderedProducts($orderedProducts, $localOrder->id);
            } catch (Exception $exception) {
                Log::error('Hiba a megrendelt termékek elmentésekor.');
                Log::error($exception->getMessage());
            }

            // Létrehozzuk az ügyfelet, ha még nincs
            $customer = $this->customerService->createCustomerFromLocalOrder($localOrder);
            if ($customer->orders()->count() == 1) {
                $this->customerService->createCall($customer->id, $localOrder->created_at);
            } else {
                $this->customerService->removeCall($customer->id);
            }

            // Mentsük el a számlát
            /** @var \App\User $reseller */
            $reseller = $localOrder->getReseller()['correct'];
            Log::info('Hozzátartozó számlázó fiók neve: '.$reseller->name);

            // Elküldjük róla a levelet is
            if ($reseller->email != 'hello@semmiszemet.hu') {
                if ($reseller->emailNotificationsEnabled()) {
                    Mail::to($reseller)->send(new NewOrder($order, $reseller));
                    Log::info('Levél elküldve az alábbi e-mail címre: '.$reseller->email);
                } else {
                    Log::info('A felhasználó nem kért e-mail értesítéseket, ezért nem küldünk. ('.$reseller->email.')');
                }
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
    public function updateProducts() {
        $this->shoprenterApi->updateProducts();
        Log::info('Termékek sikeresen frissítve a Shoprenter adatbázisából!');
    }

    /**
     *
     */
    public function testShoprenter() {
    }

    /**
     * Teszteli, hogy okos-e a billingo
     *
     * @return bool
     */
    public function testBillingo(): bool {

    }

    /**
     * @param  Request  $request
     * @return array
     */
    public function getProduct(Request $request): array {
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
     * @param $privateKey
     * @return bool|string[]
     */
    public function handleDuplicates($privateKey): array|bool {
        set_time_limit(0);
        Log::info('- Duplikált megrendelések rendberakása -');

        // Ellenőrizzük a kulcsot
        if (env('PRIVATE_KEY') != $privateKey) {
            return ['error' => 'Hibás privát kulcs lett megadva'];
        }

        $duplicates = Order::select([
            'inner_resource_id',
            DB::raw('COUNT(*) as db'),
        ])->groupBy('inner_resource_id')->having('db', '>', '1')->get()->toArray();

        foreach ($duplicates as $row) {
            $duplicatedInnerId = $row['inner_resource_id'];
            Log::info('Vizsgált inner ID: '.$duplicatedInnerId);
            foreach (Order::where('inner_resource_id', $duplicatedInnerId)->get() as $localOrder) {
                if ($localOrder->updated_at->diffInSeconds($localOrder->created_at) < 5) {
                    Log::info('Duplikált ID: '.$localOrder->id);
                    $localOrder->delete();
                }
            }
        }

        Log::info('- Duplikált megrendelések törölve -');

        return true;
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkZip(Request $request) {
        Log::info('Irányítószám ellenőrzése ShopRenter felületről...');

        $rZip     = RegionZip::whereZip($request->get('zip'))->first();
        $reseller = $rZip ? $rZip->reseller : null;

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

    public function checkMissingOrders($privateKey) {
        set_time_limit(0);
        Log::info('- Hiányzó megrendelések ellenőrzése... -');
        $missing = 0;

        // Ellenőrizzük a kulcsot
        if (env('PRIVATE_KEY') != $privateKey) {
            return ['error' => 'Hibás privát kulcs lett megadva'];
        }
        $recentOrders = $this->shoprenterApi->getRecentOrders();
        if (! $recentOrders) {
            Log::error('Nem kaptunk vissza megrendeléseket a hiányzó megrendelések ellenőrzésekor...');

            return false;
        }

        foreach ($recentOrders as $recentOrder) {
            // Ellenőrizzük le, hogy nincs-e már ilyen megrendelés, ne duplikáljunk!
            if ($this->orderService->getLocalOrderByResourceId($recentOrder['id'])) {
                // Log::warning('- Már létezik ilyen megrendelés, nem hozzuk létre...');
                continue;
            }

            // Ellenőrizzük le, hogy van a státuszáról adat.
            if (! array_key_exists('statusData', $recentOrder)) {
                Log::warning(sprintf('- Nincs adat a megrendelés státuszáról ezért nem hozzuk létre!!! (ShopRenter Azonosítós: %s)', $recentOrder['innerId']));
                continue;
            }

            // Elmentése a Megrendelések db-be
            $missing++;

            $localOrder                       = new Order();
            $localOrder->shipping_postcode    = $recentOrder['shippingPostcode'];
            $localOrder->shipping_city        = $recentOrder['shippingCity'];
            $localOrder->shipping_address     = sprintf('%s %s', $recentOrder['shippingAddress1'], $recentOrder['shippingAddress2']);
            $localOrder->inner_id             = $recentOrder['innerId'];
            $localOrder->inner_resource_id    = $recentOrder['id'];
            $localOrder->total_gross          = $recentOrder['total'];
            $localOrder->firstname            = $recentOrder['firstname'];
            $localOrder->lastname             = $recentOrder['lastname'];
            $localOrder->email                = $recentOrder['email'];
            $localOrder->phone                = $recentOrder['phone'];
            $localOrder->shipping_method_name = $recentOrder['shippingMethodName'];
            $localOrder->payment_method_name  = $recentOrder['paymentMethodName'];
            $localOrder->status_text          = $recentOrder['statusData']['name'];
            $localOrder->status_color         = $this->statusService->getColorByStatusName($recentOrder['statusData']['name']);
            $localOrder->created_at           = Carbon::createFromTimeString($recentOrder['dateCreated']);

            // Nettó összeg és ÁFA kinyerése
            foreach ($recentOrder['orderTotals'] as $orderTotal) {
                if ($orderTotal['type'] == 'SUB_TOTAL') {
                    $localOrder->total = round($orderTotal['value']);
                }
                if ($orderTotal['type'] == 'TAX') {
                    $localOrder->tax_price = round($orderTotal['value']);
                }
            }

            // Eldöntjük, hogy kapjon-e online fizetéses végső fizetés típust
            if ($localOrder->payment_method_name == 'Online bankkártyás fizetés') {
                $localOrder->final_payment_method = 'Online Bankkártya';
            }

            if (! $localOrder->save()) {
                Log::error('Nem sikerült elmenteni a megrendelést, de ennek már úgyis nyoma van.');
            }

            // Termékeket kiszedjük maszekba
            $productsList = [];
            foreach ($recentOrder['orderProducts'] as $orderProduct) {
                $productsList[] = [
                    'sku'   => $orderProduct['sku'],
                    'count' => intval($orderProduct['stock1']),
                ];
            }
            $this->orderService->saveOrderedProducts($productsList, $localOrder->id);

            // Létrehozzuk az ügyfelet, ha még nincs
            $customer = $this->customerService->createCustomerFromLocalOrder($localOrder);
            if ($customer->orders()->count() == 1) {
                $this->customerService->createCall($customer->id, $localOrder->created_at);
            } else {
                $this->customerService->removeCall($customer->id);
            }

            // Mentsük el a számlát
            /** @var \App\User $reseller */
            $reseller = $localOrder->getReseller()['correct'];
            Log::info('Hozzátartozó számlázó fiók neve: '.$reseller->name);

            // Ha nincs billingo összekötés ne hozzunk létre semmit
            if (! $this->billingoNewService->isBillingoConnected($reseller)) {
                Log::info('A viszonteladónak nincs beállítva billingo összekötés, ezért nem hozunk létre számlát.');
            } else {
                if (env('APP_ENV') != 'production') {
                    Log::info('- Nem prodon vagyunk, nem csinálunk számlákat :)');
                    continue;
                }

                // 1. Partner
                $partner = $this->billingoNewService->createPartnerNew($recentOrder, $reseller);
                if (! $partner) {
                    Log::error('Hiba történt a partner létrehozásakor, a számlát nem lehet létrehozni.');
                    continue;
                }

                // 2. Számla
                $invoice = $this->billingoNewService->createDraftInvoiceNew($recentOrder, $partner, $reseller);
                if (! $invoice) {
                    Log::error('Hiba történt a piszkozat számla létrehozásakor.');
                    continue;
                }
                Log::info(sprintf('A piszkozat számla sikeresen létrejött (Megrendelés: %s, Azonosító: %s)', $localOrder->id, $invoice->getId()));

                // 3. Elmentjük a piszkozatot
                $localOrder->draft_invoice_id = $invoice->getId();
                $localOrder->save();
                Log::info(sprintf('A piszkozat számla sikeresen elmentve a megrendeléshez (Megr. Azonosító: %s, Számla azonosító: %s)', $localOrder->id, $invoice->getId()));

                dd($invoice);
            }
            // TODO: Trackeljük Klaviyo-ba
        }

        Log::info(sprintf('Összesen %s db megrendelés került vizsgálásra, ebből %s db hiányzott.', count($recentOrders), $missing));
    }

    public function checkMissingOrdersMonthly($privateKey) {
        set_time_limit(0);
        Log::info('- Hiányzó megrendelések ellenőrzése... -');
        $missing = 0;

        // Ellenőrizzük a kulcsot
        if (env('PRIVATE_KEY') != $privateKey) {
            return ['error' => 'Hibás privát kulcs lett megadva'];
        }
        $start        = Carbon::now()->subDays(7);
        $recentOrders = $this->shoprenterApi->getOrdersSince($start);
        if (! $recentOrders) {
            Log::error('Nem kaptunk vissza megrendeléseket a hiányzó megrendelések ellenőrzésekor...');

            return false;
        }

        foreach ($recentOrders as $recentOrder) {
            // Ellenőrizzük le, hogy nincs-e már ilyen megrendelés, ne duplikáljunk!
            if ($this->orderService->getLocalOrderByResourceId($recentOrder['id'])) {
                // Log::warning('- Már létezik ilyen megrendelés, nem hozzuk létre...');
                continue;
            }

            // Ellenőrizzük le, hogy van a státuszáról adat.
            if (! array_key_exists('statusData', $recentOrder)) {
                Log::warning(sprintf('- Nincs adat a megrendelés státuszáról ezért nem hozzuk létre!!! (ShopRenter Azonosítós: %s)', $recentOrder['innerId']));
                continue;
            }

            // Elmentése a Megrendelések db-be
            $missing++;

            $localOrder                       = new Order();
            $localOrder->shipping_postcode    = $recentOrder['shippingPostcode'];
            $localOrder->shipping_city        = $recentOrder['shippingCity'];
            $localOrder->shipping_address     = sprintf('%s %s', $recentOrder['shippingAddress1'], $recentOrder['shippingAddress2']);
            $localOrder->inner_id             = $recentOrder['innerId'];
            $localOrder->inner_resource_id    = $recentOrder['id'];
            $localOrder->total_gross          = $recentOrder['total'];
            $localOrder->firstname            = $recentOrder['firstname'];
            $localOrder->lastname             = $recentOrder['lastname'];
            $localOrder->email                = $recentOrder['email'];
            $localOrder->phone                = $recentOrder['phone'];
            $localOrder->shipping_method_name = $recentOrder['shippingMethodName'];
            $localOrder->payment_method_name  = $recentOrder['paymentMethodName'];
            $localOrder->status_text          = $recentOrder['statusData']['name'];
            $localOrder->status_color         = $this->statusService->getColorByStatusName($recentOrder['statusData']['name']);
            $localOrder->created_at           = Carbon::createFromTimeString($recentOrder['dateCreated']);

            // Nettó összeg és ÁFA kinyerése
            foreach ($recentOrder['orderTotals'] as $orderTotal) {
                if ($orderTotal['type'] == 'SUB_TOTAL') {
                    $localOrder->total = round($orderTotal['value']);
                }
                if ($orderTotal['type'] == 'TAX') {
                    $localOrder->tax_price = round($orderTotal['value']);
                }
            }

            // Eldöntjük, hogy kapjon-e online fizetéses végső fizetés típust
            if ($localOrder->payment_method_name == 'Online bankkártyás fizetés') {
                $localOrder->final_payment_method = 'Online Bankkártya';
            }

            if (! $localOrder->save()) {
                Log::error('Nem sikerült elmenteni a megrendelést, de ennek már úgyis nyoma van.');
            }

            // Termékeket kiszedjük maszekba
            $productsList = [];
            foreach ($recentOrder['orderProducts'] as $orderProduct) {
                $productsList[] = [
                    'sku'   => $orderProduct['sku'],
                    'count' => intval($orderProduct['stock1']),
                ];
            }
            $this->orderService->saveOrderedProducts($productsList, $localOrder->id);

            // Létrehozzuk az ügyfelet, ha még nincs
            $customer = $this->customerService->createCustomerFromLocalOrder($localOrder);
            if ($customer->orders()->count() == 1) {
                $this->customerService->createCall($customer->id, $localOrder->created_at);
            } else {
                $this->customerService->removeCall($customer->id);
            }

            // Mentsük el a számlát
            /** @var \App\User $reseller */
            $reseller = $localOrder->getReseller()['correct'];
            Log::info('Hozzátartozó számlázó fiók neve: '.$reseller->name);

            // Ha nincs billingo összekötés ne hozzunk létre semmit
            if (! $this->billingoNewService->isBillingoConnected($reseller)) {
                Log::info('A viszonteladónak nincs beállítva billingo összekötés, ezért nem hozunk létre számlát.');
            } else {
                if (env('APP_ENV') != 'production') {
                    Log::info('- Nem prodon vagyunk, nem csinálunk számlákat :)');
                    continue;
                }

                // 1. Partner
                $partner = $this->billingoNewService->createPartnerNew($recentOrder, $reseller);
                if (! $partner) {
                    Log::error('Hiba történt a partner létrehozásakor, a számlát nem lehet létrehozni.');
                    continue;
                }

                // 2. Számla
                $invoice = $this->billingoNewService->createDraftInvoiceNew($recentOrder, $partner, $reseller);
                if (! $invoice) {
                    Log::error('Hiba történt a piszkozat számla létrehozásakor.');
                    continue;
                }
                Log::info(sprintf('A piszkozat számla sikeresen létrejött (Megrendelés: %s, Azonosító: %s)', $localOrder->id, $invoice->getId()));

                // 3. Elmentjük a piszkozatot
                $localOrder->draft_invoice_id = $invoice->getId();
                $localOrder->save();
                Log::info(sprintf('A piszkozat számla sikeresen elmentve a megrendeléshez (Megr. Azonosító: %s, Számla azonosító: %s)', $localOrder->id, $invoice->getId()));

                dd($invoice);
            }
            // TODO: Trackeljük Klaviyo-ba
        }

        Log::info(sprintf('Összesen %s db megrendelés került vizsgálásra, ebből %s db hiányzott.', count($recentOrders), $missing));
    }
}
