<?php

namespace App\Http\Controllers;

use App\MarketingResult;
use App\Subesz\RevenueService;
use App\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MarketingResultController extends Controller
{
    /** @var RevenueService */
    private $revenueService;

    /**
     * MarketingResultController constructor.
     * @param $revenueService
     */
    public function __construct(RevenueService $revenueService)
    {
        $this->revenueService = $revenueService;
    }

    /**
     * @return Factory|View
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
            'mr-date-year' => 'required',
            'mr-date-month' => 'required',
            'mr-comment' => 'required',
        ]);

        // Változók
        /** @var User $reseller */
        $topupAmount = array_key_exists('mr-topup', $data) ? floatval(trim(str_replace(' ', '', $data['mr-topup-amount']))) : 0;
        $spentAmount = floatval(trim(str_replace(' ', '', $data['mr-spent'])));
        $closedMonth = Carbon::createFromFormat('Y/m/d H:i:s', sprintf("%s/%s/01 00:00:01", $data['mr-date-year'], $data['mr-date-month']));
        $reseller = User::find($data['mr-reseller-id']);
        $oldBalance = $reseller->balance;

        // Ha nincs ilyen viszonteladó, akkor kilépünk
        if (!$reseller) {
            Log::error('Nem található ilyen felhasználó: ' . $data['mr-reseller-id']);
        }

        // 1. Feltöltjük az egyenlegét a viszonteladónak
        if (array_key_exists('mr-topup', $data)) {
            // - 1: Létrehozzuk a központnak mint bevétel, ez létrehozza a kiadást a viszonteladónak is
            $this->revenueService->storeCentralIncome(
                'Egyenlegfeltöltés',
                $reseller->id,
                $topupAmount,
                date('Y-m-d'),
                $data['mr-comment'] ?? null
            );
            $reseller->refresh(); // Frissítjük a felhasználót

            // - 2: Feltöltjük a viszonteladó egyenlegét az összeggel
            $this->revenueService->addBalance($topupAmount, $reseller);
        }

        // 2. Levonjuk az elköltött összeget
        $reseller->balance -= $spentAmount;
        $reseller->save();
        Log::info(sprintf('Viszonteladó egyenlege csökkentve %s Ft-tal marketinges eredmények elmentése miatt', $spentAmount));

        // 3. Marketing eredmény létrehozása
        $mr = new MarketingResult();
        $mr->comment = $data['mr-comment'] ?? 'Marketinges eredmények elmentve ' . $reseller->name . ' részére.';
        $mr->user_id = $reseller->id;
        $mr->old_balance = $oldBalance;
        $mr->topup_amount = $topupAmount ?? 0;
        $mr->spent = $spentAmount;
        $mr->date = $closedMonth;
        $mr->reached = intval(trim(str_replace(' ', '', $data['mr-reached'])));
        $mr->save();

        return redirect(url()->previous())->with([
            'success' => 'Marketinges eredmények sikeresen elmentve.',
        ]);
    }
}
