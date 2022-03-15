<?php

namespace App\Subesz;

use App\Delivery;
use App\Income;
use App\OrderProducts;
use App\Report;
use App\ReportProducts;
use App\User;
use Carbon\Carbon;
use DB;
use Log;

/**
 * Class ReportService
 *
 * @package App\Subesz
 */
class ReportService
{
    /**
     * @param  User    $reseller
     * @param  Carbon  $currentDate
     */
    public function generateReportByDate(User $reseller, Carbon $currentDate) {
        $endDate   = $currentDate->clone()->subMonth()->lastOfMonth()->setHour(23)->setMinute(59)->setSecond(59);
        $startDate = $endDate->clone()->firstOfMonth();

        // Kiszedjük az összes megrendelését az adott időintervallumra
        $perfStart = microtime(true);
        $orders    = $reseller->orders()->where([
            ['created_at', '>=', $startDate],
            ['created_at', '<=', $endDate],
            ['status_text', '=', 'Teljesítve'],
        ])->get();

        // Kiadások
        $expenses = $reseller->expenses()->where([
            ['created_at', '>=', $startDate],
            ['created_at', '<=', $endDate],
        ])->first([DB::raw('SUM(gross_value) as gross_expense')]);

        // Termékekdarabra
        $products = OrderProducts::select([
            'product_sku',
            DB::raw('SUM(product_qty) as count'),
        ])->whereIn('order_id', $orders->pluck('id')->toArray())->groupBy('product_sku')->get();

        // Bevételek
        $incomes = Income::where([
            ['date', '>=', $startDate],
            ['date', '<=', $endDate],
            ['user_id', '=', $reseller->id],
        ])->first([DB::raw('SUM(gross_value) as gross_income')]);

        // Kiszállítások
        $deliveries = Delivery::where([
            ['delivered_at', '>=', $startDate],
            ['delivered_at', '<=', $endDate],
            ['user_id', '=', $reseller->id],
        ])->first([DB::raw('COUNT(id) as count')]);

        // Létrehozzuk magát a riportot
        $report                   = new Report();
        $report->user_id          = $reseller->id;
        $report->gross_expense    = $expenses->gross_expense ?? 0;
        $report->gross_income     = $incomes->gross_income ?? 0;
        $report->delivered_orders = $deliveries->count;
        $report->created_at       = $startDate;

        $report->save();
        foreach ($products as $product) {
            $rp              = new ReportProducts();
            $rp->product_sku = $product->product_sku;
            $rp->report_id   = $report->id;
            $rp->product_qty = $product->count;
            $rp->save();
        }

        $perfEnd = microtime(true);
        Log::info(sprintf('%s viszonteladó havi riportja legenerálva: %ss alatt', $reseller->name, round($perfEnd - $perfStart, 2)));
    }
}
