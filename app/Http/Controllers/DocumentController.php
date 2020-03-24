<?php

namespace App\Http\Controllers;

use App\Subesz\OrderService;
use App\Subesz\ShoprenterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class DocumentController extends Controller
{
    /** @var OrderService */
    private $orderService;

    /** @var ShoprenterService */
    private $shoprenterApi;

    /**
     * DocumentController constructor.
     * @param OrderService $orderService
     * @param ShoprenterService $shoprenterService
     */
    public function __construct(OrderService $orderService, ShoprenterService $shoprenterService)
    {
        $this->orderService = $orderService;
        $this->shoprenterApi = $shoprenterService;
    }

    public function download(Request $request) {
        $data = $request->validate([
            'sm-order-ids' => 'required',
        ]);

        // Átalakítjuk a bemenetet
        $orderResourceIds = json_decode($data['sm-order-ids']);
        $orders = [];
        foreach ($orderResourceIds as $resourceId) {
            $orders[] = $this->shoprenterApi->getOrder($resourceId);

        }

        // Adjuk át view-ba
        /** @var PDF $pdf */
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('pdf.shippingmail', [
            'data' => $orders,
            'pdf' => $pdf,
        ]);

        $filename = sprintf('szs_szallitolevel_%s.pdf', date('Y_m_d_his'));
        return $pdf->download($filename);
    }
}
