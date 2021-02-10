<?php

namespace App\Subesz;


use App\OrderProducts;
use App\Report;
use App\ReportProducts;
use App\User;
use Carbon\Carbon;
use DB;
use Log;

/**
 * Class ReportService
 * @package App\Subesz
 */
class ReportService
{
    /**
     * @param User $reseller
     * @param Carbon $currentDate
     */
    public function generateReportByDate(User $reseller, Carbon $currentDate)
    {
        $endDate = $currentDate->subMonth()->lastOfMonth()->setHour(23)->setMinute(59)->setSecond(59);
        $startDate = $endDate->clone()->firstOfMonth();

        // Kiszedjük az összes megrendelését az adott időintervallumra
        $perfStart = microtime(true);
        $orders = $reseller->orders()->where([
            ['created_at', '>=', $startDate],
            ['created_at', '<=', $endDate],
            ['status_text', '=', 'Teljesítve'],
        ])->get();

        // Kiadások
        $expenses = $reseller->expenses()->where([
            ['created_at', '>=', $startDate],
            ['created_at', '<=', $endDate],
        ])->get([DB::raw('SUM(gross_value) as gross_expense')]);

        // Termékekdarabra
        $products = OrderProducts::select(['product_sku', DB::raw('SUM(product_qty) as count')])
            ->whereIn('order_id', $orders->pluck('id')->toArray())
            ->groupBy('product_sku')
            ->get();

        // Létrehozzuk magát a riportot
        $report = new Report();
        $report->user_id = $reseller->id;
        $report->gross_expense = $expenses[0]->gross_expense ?? 0;
        $report->gross_income = $orders->sum('total_gross');
        $report->delivered_orders = $orders->count();
        $report->created_at = $startDate;
        $report->save();
        foreach ($products as $product) {
            $rp = new ReportProducts();
            $rp->product_sku = $product->product_sku;
            $rp->report_id = $report->id;
            $rp->product_qty = $product->count;
            $rp->save();
        }

        $perfEnd = microtime(true);
        Log::info(sprintf('%s viszonteladó havi riportja legenerálva: %ss alatt', $reseller->name, round($perfEnd - $perfStart, 2)));
    }
}
