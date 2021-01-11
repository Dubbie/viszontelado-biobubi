<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Class Report
 * @package App
 * @mixin Report
 */
class Report extends Model
{
    /**
     * @return HasMany
     */
    public function reportProducts(): HasMany
    {
        return $this->hasMany(ReportProducts::class, 'id', 'report_id');
    }

    /**
     * @return Report|Builder|Model|object
     */
    public function getPreviousReport()
    {
        return Report::where([
            ['user_id', '=', $this->user_id],
            ['created_at', '=', $this->created_at->subMonth()]
        ])->first();
    }

    /**
     * @return bool
     */
    public function hasPrevious(): bool
    {
        return Report::where([
                ['user_id', '=', $this->user_id],
                ['created_at', '=', $this->created_at->subMonth()]
            ])->count() > 0;
    }

    /**
     * @return string
     */
    public function getIncomeDifference(): string
    {
        $prev = $this->getPreviousReport();

        if ($prev) {
            return $this->gross_income - $prev->gross_income;
        }

        return $this->gross_income;
    }

    /**
     * @return string
     */
    public function getIncomeDifferencePercent(): string
    {
        $diff = $this->getIncomeDifference();
        $prev = $this->getPreviousReport();

        if ($prev) {
            if ($prev->gross_income == 0) {
                return '';
            }

            $perc = round(($diff / $prev->gross_income) * 100);
            if ($perc > 0) {
                $perc = '+'.$perc;
            }
            return $perc.'%';
        }

        return '0%';
    }

    /**
     * @return string
     */
    public function getExpenseDifference(): string
    {
        $prev = $this->getPreviousReport();

        if ($prev) {
            return $this->gross_expense - $prev->gross_expense;
        }

        return $this->gross_expense;
    }

    /**
     * @return string
     */
    public function getExpenseDifferencePercent(): string
    {
        $diff = $this->getExpenseDifference();
        $prev = $this->getPreviousReport();

        if ($prev) {
            if ($prev->gross_expense == 0) {
                return '';
            }

            $perc = round(($diff / $prev->gross_expense) * 100);
            if ($perc > 0) {
                $perc = '+'.$perc;
            }
            return $perc.'%';
        }

        return '0%';
    }

    /**
     * @return string
     */
    public function getDeliveriesDifference(): string
    {
        $prev = $this->getPreviousReport();

        if ($prev) {
            return $this->delivered_orders - $prev->delivered_orders;
        }

        return $this->delivered_orders;
    }

    /**
     * @return string
     */
    public function getDeliveriesDifferencePercent(): string
    {
        $diff = $this->getDeliveriesDifference();
        $prev = $this->getPreviousReport();

        if ($prev) {
            if ($prev->delivered_orders == 0) {
                return '';
            }

            $perc = round(($diff / $prev->delivered_orders) * 100);
            if ($perc > 0) {
                $perc = '+'.$perc;
            }
            return $perc.'%';
        }

        return '0%';
    }

    /**
     * @return float|int
     */
    public function getAvgIncomeByDeliveries()
    {
        if ($this->delivered_orders == 0) {
            return 0;
        }
        return $this->gross_income / $this->delivered_orders;
    }

    /**
     * @return float|int
     */
    public function getAvgExpensePerDelivery()
    {
        if ($this->delivered_orders == 0) {
            return 0;
        }

        return $this->gross_expense / $this->delivered_orders;
    }

    /**
     * @return float|int|mixed
     */
    public function getDeliveryExpenseByAddress()
    {
        if ($this->delivered_orders == 0) {
            return 0;
        }

        $reseller = User::find($this->user_id);
        $start = $this->created_at;
        $end = $this->created_at->lastOfMonth();
        $sum = 0;
        $expenses = $reseller->expenses()->where([
            ['name', '=', 'Benzin'],
            ['date', '>=', $start],
            ['date', '<=', $end],
        ])->get();
        /** @var Expense $expense */
        foreach ($expenses as $expense) {
            $sum += $expense->gross_value;
        }
        return $sum / $this->delivered_orders;
    }
}
