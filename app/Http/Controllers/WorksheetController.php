<?php

namespace App\Http\Controllers;

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
 * @package App\Http\Controllers
 */
class WorksheetController extends Controller
{
    /**
     * @param  Request  $request
     * @return Application|RedirectResponse|Redirector
     */
    public function add(Request $request) {
        $data = $request->validate([
            'order-id' => 'required|numeric'
        ]);

        // Elmentjük az adatbázisba, ha még nincs
        if (Worksheet::where([
            ['order_id', '=', $data['order-id']],
            ['user_id', '=', Auth::id()],
        ])->count() > 0) {
            Log::info('Nem mentjük el a munkalapra a megrendelést, mert már szerepel benne');
            return redirect(url()->previous())->with([
                'error' => 'A megrendelés már szerepel a munkalapon'
            ]);
        }

        // Hozzáadás
        $wse = new Worksheet();
        $wse->user_id = Auth::id();
        $wse->order_id = $data['order-id'];
        $wse->save();

        Log::info('Munkalapra mentés:');
        Log::info(' - Felhasználó: ' . Auth::user()->name);
        Log::info(' - Megrendelés: ' . $data['order-id']);

        return redirect(url()->previous())->with([
            'success' => 'Megrendelés hozzáaadva a munkalaphoz'
        ]);
    }

    /**
     * @param  Request  $request
     * @return Application|RedirectResponse|Redirector
     */
    public function remove(Request $request) {
        $data = $request->validate([
            'ws-id' => 'required|numeric'
        ]);

        // Megkeressük, hogy van-e már a db-be
        $wse = Worksheet::find($data['ws-id']);
        if (!$wse) {
            Log::info(sprintf('A megrendelést nem tudjuk kiszedni mivel nincs a munkalapon. (Munkalap azonosító: %s)', $data['ws-id']));
            return redirect(url()->previous())->with([
                'error' => 'A megrendelés nem szerepel a munkalapon'
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
            'success' => 'Megrendelés eltávolítva a munkalapról'
        ]);
    }
}
