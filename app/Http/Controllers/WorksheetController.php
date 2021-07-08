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

        // Hozzáadás
        $wse           = new Worksheet();
        $wse->user_id  = Auth::id();
        $wse->order_id = $data['order-id'];
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
                $wse->save();

                Log::info('Munkalapra mentés:');
                Log::info(' - Felhasználó: '.Auth::user()->name);
                Log::info(' - Megrendelés: '.$order->id);

                $added++;
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
            $wse->delete();
        } catch (Exception $e) {
            Log::error('Hiba történt a munkalap bejegyzés törlésekor.');
        }

        Log::info(sprintf('Megrendelés eltávolítva a munkalapról (Megr. azonosító: %s, Felhasználó: %s)', $data['ws-id'], $user->name));

        return redirect(url()->previous())->with([
            'success' => 'Megrendelés eltávolítva a munkalapról',
        ]);
    }
}
