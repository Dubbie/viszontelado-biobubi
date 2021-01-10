<?php

namespace App\Http\Controllers;

use App\Subesz\RevenueService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MarketingResultController extends Controller
{
    /** @var RevenueService */
    private $revenueService;

    /**
     * MarketingResultController constructor.
     * @param $revenueService
     */
    public function __construct($revenueService)
    {
        $this->revenueService = $revenueService;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show()
    {
        return view('hq.marketing')->with([
            'resellers' => User::all(),
        ]);
    }

    /**
     * @param Request $request
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'mr-reseller-id' => 'required',
            'mr-topup' => 'nullable',
            'mr-topup-amount' => 'nullable|required_with:mr-topup',
            'mr-spent' => 'required',
            'mr-reached' => 'required',
            'mr-comment' => 'nullable',
        ]);

        /** @var User $reseller */
        $reseller = User::find($data['mr-reseller-id']);
        if (!$reseller) {
            Log::error('Nem található ilyen felhasználó: ' . $data['mr-reseller-id']);
        }

        // 1. Feltöltjük az egyenlegét a viszonteladónak
        if (array_key_exists('mr-topup-amount', $data)) {
            // - 1.1: Létrehozzuk a viszonteladó kiaádását
            $this->revenueService->storeResellerExpense(
                'Egyenlegfeltöltés',
                $data['mr-topup-amount'],
                $reseller,
                $data['mr-comment'] ?? null
            );
        }
    }
}
