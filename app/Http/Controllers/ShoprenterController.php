<?php

namespace App\Http\Controllers;

use App\Subesz\ShoprenterService;
use Illuminate\Http\Request;

class ShoprenterController extends Controller
{
    /** @var ShoprenterService */
    private $shoprenterApi;

    /**
     * ShoprenterController constructor.
     * @param ShoprenterService $shoprenterService
     */
    public function __construct(ShoprenterService $shoprenterService)
    {
        $this->shoprenterApi = $shoprenterService;
    }

    /**
     * @param $privateKey
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateOrders($privateKey) {
        // Ellenőrizzük a kulcsot
        if (env('PRIVATE_KEY') != $privateKey) {
            return redirect(action('OrderController@index'))->with([
                'error' => 'Hibás privát kulcs lett megadva',
            ]);
        }

        $osds = $this->shoprenterApi->getAllStatuses();

        $statusMap = [];
        foreach ($osds->items as $osd) {
            $orderStatusId = str_replace(sprintf('%s/orderStatuses/', env('SHOPRENTER_API')), '', $osd->orderStatus->href);

            $statusMap[$orderStatusId] = [
                'name' => $osd->name,
                'color' => $osd->color,
            ];
        }

        $orders = $this->shoprenterApi->getAllOrders();
        $succesCount = 0;
        foreach ($orders as $order) {
            if ($this->shoprenterApi->updateLocalOrder($order)) {
                $succesCount++;
            }
        }

        if ($succesCount == count($orders)) {
            return redirect(action('OrderController@index'))->with([
                'success' => sprintf('%s db megrendelés sikeresen frissítve', $succesCount),
            ]);
        } else {
            return redirect(action('OrderController@index'))->with([
                'error' => 'Hiba történt a megrendelések frissítésekor',
            ]);
        }
    }

    /**
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

        $array = json_decode($request->input('data'), true);
        Log::info(sprintf('-- Megrendelések száma: %s db', count($array['orders']['order'])));
        foreach ($array['orders']['order'] as $_order) {
            // Elmentése a Megrendelésnek db-be
            $order = new Order();
            $order->shipping_postcode = $_order['shippingPostcode'];
            $order->shipping_city = $_order['shippingCity'];
            $order->shipping_address = sprintf('%s %s', $_order['shippingAddress1'], $_order['shippingAddress2']);
            $order->inner_id = $_order['innerId'];
            $order->inner_resource_id = $_order['innerResourceId'];
            $order->total = $_order['total'];
            $order->total_gross = $_order['totalGross'];
            $order->tax_price = $_order['taxPrice'];
            $order->firstname = $_order['firstname'];
            $order->lastname = $_order['lastname'];
            $order->email = $_order['email'];
            $order->shipping_method_name = $_order['shippingMethodName'];
            $order->payment_method_name = $_order['paymentMethodName'];
            $order->status_text = $_order['orderHistory']['statusText'];
            $order->status_color = '#ff00ff';
            $order->created_at = date('Y-m-d H:i:s', strtotime($_order['orderCreated']));

            $order->save();
        }

        return ['success' => true];
    }
}
