<?php

namespace App\Http\Controllers;

use App\Report;
use App\ReportProducts;
use App\Subesz\ReportService;
use App\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Log;
use Schema;

class ReportController extends Controller
{
    /**
     * @return Factory|View
     */
    public function showQuick() {
        return view('report.quick')->with([
            'deliveredCount' => Auth::user()->getDeliveryCountThisMonth(),
        ]);
    }

    /**
     * @param  Request  $request
     * @return Application|Factory|View
     */
    public function showMonthly(Request $request) {
        $date              = $request->input('date') ?? null;
        $selectedReport    = null;
        $selectedMarketing = null;

        if ($date) {
            $carbonDate        = Carbon::createFromFormat('Y-m-d H:i:s', $date.'-01 00:00:01');
            $selectedReport    = Auth::user()->reports()->whereDate('created_at', '=', $carbonDate->format('Y-m-d'))->first();
            $selectedMarketing = Auth::user()->marketingResults()->whereDate('date', '=', $carbonDate->format('Y-m-d'))->first();
        }

        return view('report.monthly')->with([
            'selectedReport'    => $selectedReport,
            'selectedMarketing' => $selectedMarketing,
        ]);
    }

    /**
     * @return mixed
     * Megadja az összes évet, melyben található report DB-ben.
     */
    public static function allYears() {
        return \DB::table('reports')->selectRaw("DATE_FORMAT(created_at, '%Y') AS year, COUNT(*) AS total")->groupBy('year')->orderByDesc('year')->get();
    }

    /**
     * @param $privateKey
     * @return array|string[]
     */
    public function generateMonthlyReports($privateKey): array {
        Log::info('Havi riportok generálásának megkezdése...');
        $start = microtime(true);

        // Ellenőrizzük a kulcsot
        if (env('PRIVATE_KEY') != $privateKey) {
            return ['error' => 'Hibás privát kulcs lett megadva'];
        }

        /** @var User $reseller */
        /** @var ReportService $repService */
        $repService = resolve('App\Subesz\ReportService');
        foreach (User::withCount('zips')->get() as $reseller) {
            if ($reseller->zips_count == 0) {
                Log::info('- %s nem viszonteladó, mivel nincs hozzárendelve irányítószám, ezért kihagyjuk.');
                continue;
            }

            $repService->generateReportByDate($reseller, Carbon::now());
        }

        return ['success' => sprintf('... Havi riportok sikeresen létrehozva összesen %ss alatt.', round(microtime(true) - $start, 2))];
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function regenerateReports() {
        Log::info('Havi riportok újragenerálásának megkezdése...');
        $start = microtime(true);

        Log::info('---');
        Log::info('Havi riportok törlése...');

        Schema::disableForeignKeyConstraints();
        ReportProducts::truncate();
        Report::truncate();
        Schema::enableForeignKeyConstraints();

        Log::info('Havi riportok törlése sikeres!');
        Log::info('---');

        $rsDate = Carbon::create(2021, 2);
        $now    = Carbon::now()->addMonth();
        while ($rsDate <= $now) {
            /** @var User $reseller */
            /** @var ReportService $repService */
            $repService = resolve('App\Subesz\ReportService');
            foreach (User::withCount('zips')->get() as $reseller) {
                if ($reseller->zips_count == 0) {
                    Log::info(sprintf('- %s nem viszonteladó, mivel nincs hozzárendelve irányítószám, ezért kihagyjuk.', $reseller->name));
                    continue;
                }

                $repService->generateReportByDate($reseller, $rsDate);
            }

            $rsDate->addMonth();
        }

        return redirect(action('RevenueController@hqFinance'))->with([
            'success' => sprintf('... Havi riportok sikeresen létrehozva összesen %ss alatt.', round(microtime(true) - $start, 2)),
        ]);
    }
}
