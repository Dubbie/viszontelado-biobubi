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
        // Cron:
        // Tíz percenként frissít
        // */10 * * * * wget -O - "http://127.0.0.1:8000/megrendelesek/frissites/yXhYh8Dt4Fz2djgv" >/dev/null 2>&1
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
}
