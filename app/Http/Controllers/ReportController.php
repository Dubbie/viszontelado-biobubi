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
     * @return Application|Factory|View
     */
    public function showMonthly()
    {
        return view('report.monthly')->with([

        ]);
    }
}
