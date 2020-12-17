<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showQuick() {
        return view('report.quick')->with([
            'deliveredCount' => \Auth::user()->getDeliveryCountThisMonth(),
        ]);
    }
}
