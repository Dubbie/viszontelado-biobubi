<?php

namespace App\Http\Controllers;

use App\Order;
use App\Subesz\OrderService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RevenueController extends Controller
{
    /** @var OrderService */
    private $orderService;

    /**
     * RevenueController constructor.
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function income()
    {
        $query = $this->orderService->getOrdersQueryByUserId(Auth::id());

//        dd($query->get());
        $data = $query->where('status_text', 'Teljesítve')->where('created_at', '>=', Carbon::now()->firstOfMonth())
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->get([
                DB::raw('Date(created_at) as date'),
                DB::raw('SUM(`total_gross`) as "total"')
            ]);

        return view('revenue.income')->with([
            'incomeData' => $data->toArray(),
        ]);
    }
}
