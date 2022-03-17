<?php

namespace App\Http\Controllers;

use App\CustomerCall;
use Auth;
use Exception;
use Log;

class CustomerCallController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index() {
        if (CustomerCall::count() == 0) {
            $cs = resolve('App\Subesz\CustomerService');
            $cs->createInitialTimers();
        }

        return view('call.index')->with([
            'calls' => Auth::user()->calls()->orderBy('called_at')->orderBy('due_date')->paginate(50),
        ]);
    }

    /**
     * @param $callId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function complete($callId) {
        /** @var CustomerCall $call */
        $call = Auth::user()->calls()->find($callId);
        if (! $call) {
            return redirect(action([
                CustomerCallController::class,
                'index',
            ]))->with(['error' => 'Nincs ilyen azonosítójú hívandó ügyfél a fiókodhoz kapcsolva.']);
        }

        $call->called_at = date('Y-m-d H:i:s');
        $call->save();

        return redirect(url()->previous())->with(['success' => 'Hívandó sikeresen lezárva!']);
    }

    /**
     * @param $callId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function uncomplete($callId) {
        /** @var CustomerCall $call */
        $call = Auth::user()->calls()->find($callId);
        if (! $call) {
            return redirect(action([
                CustomerCallController::class,
                'index',
            ]))->with(['error' => 'Nincs ilyen azonosítójú hívandó ügyfél a fiókodhoz kapcsolva.']);
        }

        $call->called_at = null;
        $call->save();

        return redirect(url()->previous())->with(['success' => 'Hívandó sikeresen visszanyitva!']);
    }

    /**
     * @param $callId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function delete($callId) {
        /** @var CustomerCall $call */
        $call = Auth::user()->calls()->find($callId);
        if (! $call) {
            return redirect(action([
                CustomerCallController::class,
                'index',
            ]))->with(['error' => 'Nincs ilyen azonosítójú hívandó ügyfél a fiókodhoz kapcsolva.']);
        }

        try {
            $call->delete();
        } catch (Exception $e) {
            Log::error('Hiba történt a hívandó törlésekor.');

            return redirect(url()->previous())->with(['error' => 'Hiba történt a hívandó törlésekor. Kérlek jelezd egy adminisztrátornak!']);
        }

        return redirect(url()->previous())->with(['success' => 'Hívandó sikeresen törölve!']);
    }
}
