<?php

namespace App\Subesz;


use App\OrderProducts;
use App\Report;
use App\ReportProducts;
use App\User;
use Carbon\Carbon;

class ReportService
{
    /**
     * @param User $reseller
     * @param Carbon $currentDate
     */
    public function generateReportByDate(User $reseller, Carbon $currentDate)
    {
        $endDate = $currentDate->firstOfMonth();
        $startDate = $endDate->clone()->subMonth();

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
        ])->get([\DB::raw('SUM(gross_value) as gross_expense')]);

        // Termékekdarabra
        $products = OrderProducts::select(['product_sku', \DB::raw('SUM(product_qty) as count')])->where([
            ['order_id', '=', '4233']
        ])->groupBy('product_sku')->get();

        $report = new Report();
        $report->user_id = $reseller->id;
        $report->gross_expense = $expenses[0]->gross_expense ?? 0;
        $report->gross_income = $orders->sum('total_gross');
        $report->delivered_orders = $orders->count();
        $report->save();
        foreach ($products as $product) {
            $rp = new ReportProducts();
            $rp->product_sku = $product->product_sku;
            $rp->report_id = $report->id;
            $rp->product_qty = $product->count;
            $rp->save();
        }

        $perfEnd = microtime(true);
        dd(sprintf('Havi riport legenerálva: %ss alatt', round($perfEnd - $perfStart, 2)));
    }
}