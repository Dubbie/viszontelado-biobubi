<?php

namespace App\Http\Controllers;

use App\Subesz\OrderService;
use App\Worksheet;
use Auth;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
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
        $nextOrder = Auth::user()->worksheet->last()->order + 1;

        // Hozzáadás
        $wse           = new Worksheet();
        $wse->user_id  = Auth::id();
        $wse->order_id = $data['order-id'];
        $wse->order    = $nextOrder;
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
        $lastOrder = Auth::user()->worksheet->last()->ws_order;

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
                $wse->order    = $lastOrder + 1;
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
                $ws->order = $ws->ws_order - 1;
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
}
