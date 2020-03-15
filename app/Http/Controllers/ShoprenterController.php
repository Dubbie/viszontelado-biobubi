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

    public function updateOrders() {
        $osds = $this->shoprenterApi->getAllStatuses();

        $statusMap = [];
        foreach ($osds->items as $osd) {
            $orderStatusId = str_replace(sprintf('%s/orderStatuses/', env('SHOPRENTER_API')), '', $osd->orderStatus->href);

            $statusMap[$orderStatusId] = [
                'name' => $osd->name,
                'color' => $osd->color,
            ];
        }

        dump($statusMap);
        foreach ($this->shoprenterApi->getAllOrders() as $order) {
            $tax = ($order->paymentMethodTaxRate + 100) / 100;
            $total = $order->total / $tax;
            $taxPrice = intval($order->total) - $total;
            $totalGross = intval($order->total);
            echo $order->shippingPostcode . '<br>';
            echo $order->shippingCity . '<br>';
            echo $order->shippingAddress1 . '<br>';
            echo $order->innerId . '<br>';
            echo $order->id . '<br>';
            echo $total . '<br>';
            echo $totalGross . '<br>';
            echo $taxPrice . '<br>';
            echo $order->firstname . '<br>';
            echo $order->lastname . '<br>';
            echo $order->email . '<br>';
            echo $order->shippingMethodName . '<br>';
            echo $order->paymentMethodName . '<br>';
            if ($order->orderStatus) {
                $orderStatusId = str_replace(sprintf('%s/orderStatuses/', env('SHOPRENTER_API')), '', $order->orderStatus->href);
                echo $statusMap[$orderStatusId]['name'] . '<br>';
            }
            echo '<hr>';
        }
    }

    public function updateOrderStatuses() {
        $osds = $this->shoprenterApi->getAllStatuses();

        foreach ($osds->items as $osd) {
            $orderStatusId = str_replace(sprintf('%s/orderStatuses/%s', env('SHOPRENTER_API')), '', $osd->orderStatus->href);
            echo $orderStatusId;
        }
    }
}
