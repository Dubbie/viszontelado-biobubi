<?php

namespace App\Subesz;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RevenueService
{
    /** @var OrderService */
    private $orderService;

    /**
     * RevenueService constructor.
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * @param Carbon $start
     * @param Carbon $end
     * @return array
     */
    public function getIncomeByRange($start, $end) {
        // Alap lekérés a jelenlegi felhasználóhoz
        $query = $this->orderService->getOrdersQueryByUserId(Auth::id());

        $data = [];
        $dayDiff = $start->diffInDays($end);
        $current = $start->copy();
        for ($i = 0; $i <= $dayDiff; $i++) {
            $data[] = [
                'date' => $current->format('Y-m-d'),
                'count' => 0,
                'total' => '0',
            ];

            $current->addDay();
        }

        // Visszanyerjük a megfelelő lekérdezéssel
        $result = $query->where('status_text', 'Teljesítve')
            ->where([
                ['created_at', '>=', $start],
                ['created_at', '<=', $end],
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get([
                DB::raw('Date(created_at) as "date"'),
                DB::raw('COUNT(*) AS "count"'),
                DB::raw('SUM(`total_gross`) as "total"')
            ])->toArray();

        $sum = 0;
        foreach ($result as $stat) {
            $index = array_search($stat['date'], array_column($data, 'date'));

            $data[$index]['count'] = $stat['count'];
            $data[$index]['total'] = $stat['total'];

            $sum += $stat['total'];
        }

        return [
            'data' => $data,
            'sum' => $sum,
        ];
    }
}