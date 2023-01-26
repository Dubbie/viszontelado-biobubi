<?php

namespace App\Http\Controllers;

use App\Document;
use App\Product;
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
        $orders = $this->shoprenterApi->getBatchedOrdersByResourceIds(json_decode($data['sm-order-ids']));
        if (empty($orders)) {
            return redirect(url()->previous())->with([
                'error' => 'Nem kaptunk vissza adatokat a Shoprentertől. Próbáld újra később.'
            ]);
        }

        // Összegzés
        $sum = [
            'shipping' => 0,
            'income'   => 0,
            'discount' => 0,
            'items'    => [],
        ];

        foreach ($orders as $order) {
            foreach ($order->orderProducts as $item) {
                // Megnézzük, hogy van-e ilyen termék nálunk, ha igen, akkor nézzük meg, hogy csomag-e
                $localProduct = Product::where('sku', $item->sku)->first();
                $pieces       = [];

                if ($localProduct && $localProduct->subProducts()->count() > 0) {
                    // Ha ez egy csomag, akkor szedjük darabokra
                    foreach ($localProduct->subProducts as $subProduct) {
                        $pieces[] = [
                            'sku'   => $subProduct->product_sku,
                            'name'  => $subProduct->product->name,
                            'count' => $subProduct->product_qty * $item->stock1,
                        ];
                    }
                } else {
                    if (! $localProduct) {
                        // Nincs nálunk ilyen termék az adatbázisban...
                        \Log::info(sprintf('- A(z) %s termék cikkszáma nem szerepel a helyi adatbázisban! (Cikkszám: %s)', $item->name, $item->sku));
                    }

                    $pieces[] = [
                        'sku'   => $item->sku,
                        'name'  => $item->name,
                        'count' => $item->stock1,
                    ];
                }

                // Most nézzük meg az összes darabot, hogy szerepel-e már a szummázó tömbben
                foreach ($pieces as $piece) {
                    $itemIndex = array_search($piece['sku'], array_column($sum['items'], 'sku'));
                    if ($itemIndex === false) {
                        $sum['items'][] = [
                            'sku'   => $piece['sku'],
                            'name'  => $piece['name'],
                            'count' => intval($piece['count']),
                        ];
                    } else {
                        $sum['items'][$itemIndex]['count'] += intval($piece['count']);
                    }
                }
            }

            // Összegző iteráció
            foreach ($order->orderTotals as $total) {
                if ($total->type == 'TOTAL') {
                    $sum['income'] += floatval($total->value);
                }
                if ($total->type == 'SHIPPING' && intval($total->value) > 0) {
                    $sum['shipping'] += floatval($total->value);
                }
            }
        }

        // Adjuk át view-ba
        /** @var PDF $pdf */
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('pdf.shippingmail-new', [
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
