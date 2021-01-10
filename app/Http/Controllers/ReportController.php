<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * @return Factory|View
     */
    public function showQuick()
    {
        return view('report.quick')->with([
            'deliveredCount' => Auth::user()->getDeliveryCountThisMonth(),
        ]);
    }

    /**
     * @param  Request  $request
     * @return Application|Factory|View
     */
    public function showMonthly(Request $request)
    {
        $reportId = $request->input('report-id') ?? null;
        if ($reportId && !Auth::user()->reports()->find($reportId)) {
            return redirect(action('ReportController@showMonthly'))->with([
                'error' => 'Nem tartozik ilyen azonosítójú riport a felhasználóhoz',
            ]);
        }

        $selectedReport = $reportId ? Auth::user()->reports()->find($reportId) : null;
        return view('report.monthly')->with([
            'selectedReport' => $selectedReport
        ]);
    }
}
