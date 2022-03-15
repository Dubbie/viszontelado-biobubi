<?php

namespace App\Http\Controllers;

use App\Document;
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
     *
     * @param  OrderService       $orderService
     * @param  ShoprenterService  $shoprenterService
     */
    public function __construct(OrderService $orderService, ShoprenterService $shoprenterService) {
        $this->orderService  = $orderService;
        $this->shoprenterApi = $shoprenterService;
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function download(Request $request) {
        $data = $request->validate([
            'sm-order-ids' => 'required',
        ]);

        // Átalakítjuk a bemenetet
        $orderResourceIds = json_decode($data['sm-order-ids']);
        $orders           = [];
        foreach ($orderResourceIds as $resourceId) {
            $orders[] = $this->shoprenterApi->getOrder($resourceId);
        }

        foreach ($orders as $order) {
            if (! property_exists($order['order'], 'paymentLastname')) {
                \Log::error('Nem volt található név a megrendelésben (Hiba van valahol)');
                \Log::error(var_dump($order['order']));

                return redirect(url()->previous())->with([
                    'error' => 'Hiba történt a szállítólevél generálásakor. A Shoprenter API nem tért vissza megrendelésekkel.',
                ]);
            }
        }

        // Összegzés
        $sum = [
            'income'   => 0,
            'discount' => 0,
            'items'    => [],
        ];
        foreach ($orders as $order) {
            // Egyéni
            foreach ($order['products']->items as $item) {
                $itemIndex = array_search($item->sku, array_column($sum['items'], 'sku'));

                if ($itemIndex === false) {
                    $sum['items'][] = [
                        'sku'   => $item->sku,
                        'name'  => $item->name,
                        'total' => floatval($item->total),
                        'count' => intval($item->stock1),
                    ];
                } else {
                    $sum['items'][$itemIndex]['total'] += floatval($item->total);
                    $sum['items'][$itemIndex]['count'] += intval($item->stock1);
                }
            }

            // Összegző iteráció
            foreach ($order['totals'] as $total) {
                if ($total->type == 'TOTAL') {
                    $sum['income'] += floatval($total->value);
                    break;
                }
            }
        }

        // Adjuk át view-ba
        /** @var PDF $pdf */
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('pdf.shippingmail', [
            'data' => $orders,
            'pdf'  => $pdf,
            'sum'  => $sum,
        ]);

        $filename = sprintf('szs_szallitolevel_%s.pdf', date('Y_m_d_his'));

        return $pdf->download($filename);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index() {
        return view('documents')->with([
            'documents' => Document::all(),
        ]);
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request) {
        $data = $request->validate([
            'documents' => 'required',
        ]);

        /** @var \Illuminate\Http\UploadedFile $file */
        foreach ($data['documents'] as $file) {
            $path = $file->store('/public/documents');

            if (! $path) {
                return redirect(url()->previous())->with([
                    'error' => 'Hiba történt a fájl feltöltésekor',
                ]);
            }

            $doc       = new Document();
            $doc->name = $file->getClientOriginalName();
            $doc->path = $path;
            $doc->save();
        }

        return redirect(url()->previous())->with([
            'success' => 'Dokumentumok sikeresen feltöltve',
        ]);
    }

    /**
     * @param $documentId
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function getDocument($documentId) {
        $doc = Document::find($documentId);

        return $doc->download();
    }

    /**
     * @param $documentId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function deleteDocument($documentId) {
        $doc = Document::find($documentId);

        if (\Storage::delete($doc->path)) {
            $doc->delete();
        }

        return redirect(url()->previous())->with([
            'success' => 'Dokumentumok sikeresen törölve',
        ]);
    }
}
