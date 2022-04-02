<?php

namespace App\Http\Controllers;

use App\CustomerCall;
use App\Subesz\CustomerService;
use App\User;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Log;

class CustomerCallController extends Controller
{
    /** @var \App\Subesz\CustomerService */
    private $customerService;

    public function __construct(CustomerService $customerService) {
        $this->customerService = $customerService;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request) {
        if (Auth::user()->admin && CustomerCall::count() == 0) {
            try {
                $this->customerService->createInitialTimers();
            } catch (Exception $e) {
                Log::error('Hiba a hívandók generálásakor');
            }
        }

        $filter = [];

        if ($request->has('filter-reseller')) {
            $filter['reseller'] = $request->input('filter-reseller');
        }
        if ($request->has('filter-status')) {
            $filter['status'] = $request->input('filter-status');
        }
        if ($request->has('filter-query')) {
            $filter['query'] = $request->input('filter-query');
        }

        $cc = $this->customerService->getCustomerCallsFiltered($filter);

        $resellers = [];
        foreach (User::all() as $u) {
            if ($u->id == Auth::id()) {
                continue;
            }

            $resellers[] = $u;
        }

        return view('call.index')->with([
            'filter'    => $filter,
            'calls'     => $cc,
            'resellers' => $resellers,
        ]);
    }

    /**
     * @param $callId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function complete($callId) {
        /** @var CustomerCall $call */
        if (Auth::user()->admin) {
            $call = CustomerCall::find($callId);
        } else {
            $call = Auth::user()->calls()->find($callId);
        }

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
        if (Auth::user()->admin) {
            $call = CustomerCall::find($callId);
        } else {
            $call = Auth::user()->calls()->find($callId);
        }

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
        if (Auth::user()->admin) {
            $call = CustomerCall::find($callId);
        } else {
            $call = Auth::user()->calls()->find($callId);
        }

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
