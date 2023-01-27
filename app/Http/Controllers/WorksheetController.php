<?php

namespace App\Http\Controllers;

use App\Product;
use App\Subesz\OrderService;
use App\Worksheet;
use Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\App;
use Log;

/**
 * Class WorksheetController
 *
 * @package App\Http\Controllers
 */
class WorksheetController extends Controller
{
    /** @var \App\Subesz\OrderService */
    private $orderService;

    /**
     * WorksheetController constructor.
     *
     * @param  \App\Subesz\OrderService  $orderService
     */
    public function __construct(OrderService $orderService) {
        $this->orderService = $orderService;
    }

    /**
     * @param  Request  $request
     * @return Application|RedirectResponse|Redirector
     */
    public function add(Request $request) {
        $data = $request->validate([
            'order-id' => 'required|numeric',
        ]);

        // Elmentjük az adatbázisba, ha még nincs
        if (Worksheet::where([
                ['order_id', '=', $data['order-id']],
                ['user_id', '=', Auth::id()],
            ])->count() > 0) {
            Log::info('Nem mentjük el a munkalapra a megrendelést, mert már szerepel benne');

            return redirect(url()->previous())->with([
                'error' => 'A megrendelés már szerepel a munkalapon',
            ]);
        }

        // Megkeressük, mi volt a legutolsó a sorban
        $nextOrder = Auth::user()->worksheet()->count() > 0 ? Auth::user()->worksheet->last()->order + 1 : 0;

        // Hozzáadás
        $wse           = new Worksheet();
        $wse->user_id  = Auth::id();
        $wse->order_id = $data['order-id'];
        $wse->ws_order = $nextOrder;
        $wse->save();

        Log::info('Munkalapra mentés:');
        Log::info(' - Felhasználó: '.Auth::user()->name);
        Log::info(' - Megrendelés: '.$data['order-id']);

        return redirect(url()->previous())->with([
            'success' => 'Megrendelés hozzáaadva a munkalaphoz',
        ]);
    }

    /**
     * @param  Request  $request
     * @return Application|RedirectResponse|Redirector
     */
    public function addMultiple(Request $request) {
        $data = $request->validate([
            'mws-order-ids' => 'required',
        ]);

        // Átalakítjuk a bemenetet
        $orderResourceIds = json_decode($data['mws-order-ids']);
        $orders           = [];
        foreach ($orderResourceIds as $resourceId) {
            $orders[] = $this->orderService->getLocalOrderByResourceId($resourceId);
        }

        // Elmentjük az adatbázisba, ha még nincs
        $added        = 0;
        $alreadyThere = 0;

        // Megkeressük, mi volt a legutolsó a sorban
        $lastOrder = Auth::user()->worksheet()->count() > 0 ? Auth::user()->worksheet->last()->ws_order : -1;

        /** @var \App\Order $order */
        foreach ($orders as $order) {
            if (Worksheet::where([
                    ['order_id', '=', $order->id],
                    ['user_id', '=', Auth::id()],
                ])->count() > 0) {
                Log::info('Nem mentjük el a munkalapra a megrendelést, mert már szerepel benne');
                $alreadyThere++;
            } else {
                // Hozzáadás
                $wse           = new Worksheet();
                $wse->user_id  = Auth::id();
                $wse->order_id = $order->id;
                $wse->ws_order = $lastOrder + 1;
                $wse->save();

                Log::info('Munkalapra mentés:');
                Log::info(' - Felhasználó: '.Auth::user()->name);
                Log::info(' - Megrendelés: '.$order->id);

                $added++;
                $lastOrder++;
            }
        }

        $msgOut = sprintf('%s megrendelés hozzáadva a munkalaphoz.', $added);
        if ($alreadyThere > 0) {
            $msgOut .= sprintf(' (%s már szerepel a listán)', $alreadyThere);
        }

        return redirect(url()->previous())->with([
            'success' => $msgOut,
        ]);
    }

    /**
     * @param  Request  $request
     * @return Application|RedirectResponse|Redirector
     */
    public function remove(Request $request) {
        $data = $request->validate([
            'ws-id' => 'required|numeric',
        ]);

        // Megkeressük, hogy van-e már a db-be
        $wse = Worksheet::find($data['ws-id']);
        if (! $wse) {
            Log::info(sprintf('A megrendelést nem tudjuk kiszedni mivel nincs a munkalapon. (Munkalap azonosító: %s)', $data['ws-id']));

            return redirect(url()->previous())->with([
                'error' => 'A megrendelés nem szerepel a munkalapon',
            ]);
        }

        // Törlés
        $user = $wse->user;
        try {
            $tgtOrder = $wse->ws_order;
            $wse->delete();

            foreach (Auth::user()->worksheet()->where('ws_order', '>', $tgtOrder)->orderBy('ws_order')->get() as $ws) {
                $ws->ws_order = $ws->ws_order - 1;
                $ws->save();
            }
        } catch (Exception $e) {
            Log::error('Hiba történt a munkalap bejegyzés törlésekor.');
        }

        Log::info(sprintf('Megrendelés eltávolítva a munkalapról (Megr. azonosító: %s, Felhasználó: %s)', $data['ws-id'], $user->name));

        return redirect(url()->previous())->with([
            'success' => 'Megrendelés eltávolítva a munkalapról',
        ]);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function updateOrdering(Request $request) {
        $data = $request->validate([
            'ws-ids' => 'array',
        ]);

        $worksheets = Auth::user()->worksheet()->whereIn('id', $data['ws-ids'])->get();
        foreach ($worksheets as $worksheet) {
            $newOrder            = array_search($worksheet->id, $data['ws-ids']);
            $worksheet->ws_order = $newOrder;
            $worksheet->save();
        }

        return true;
    }

    public function downloadShippingMail() {
        $wsResourceIds = [];

        foreach (Auth::user()->worksheet as $wse) {
            $wsResourceIds[] = $wse->localOrder->inner_resource_id;
        }
        $ss = resolve('App\Subesz\ShoprenterService');
        $orders = $ss->getBatchedOrdersByResourceIds($wsResourceIds);

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
}
