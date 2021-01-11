<?php

namespace App\Subesz;

use App\Expense;
use App\Income;
use App\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Log;

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
     * @param Carbon|CarbonInterface $start
     * @param Carbon|CarbonInterface $end
     * @param null $userId
     * @return array
     */
    public function getIncomeByRange($start, $end, $userId = null)
    {
        // Alap lekérés a jelenlegi felhasználóhoz
        $query = null;

        if ($userId) {
            $query = $this->orderService->getOrdersQueryByUserId($userId);
        } else {
            $query = $this->orderService->getOrdersQueryByUserId(Auth::id());
        }

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

    /**
     * @param Carbon|CarbonInterface $start
     * @param Carbon|CarbonInterface $end
     * @param $userId
     * @return array
     */
    public function getExpenseByRange($start, $end, $userId)
    {
        // Visszanyerjük a megfelelő lekérdezéssel
        $result = Expense::select(['id', 'name', 'gross_value', 'date', 'comment', 'user_id'])->where([
            ['user_id', '=', $userId],
            ['date', '>=', $start],
            ['date', '<=', $end],
        ])->orderBy('date', 'DESC')
            ->get();

        $sum = 0;
        /** @var Expense $expense */
        foreach ($result as $expense) {
            $sum += $expense->gross_value;
        }

        $data = $result;

        return [
            'data' => $data,
            'sum' => $sum,
        ];
    }

    /**
     * @param $name
     * @param $resellerId
     * @param $amount
     * @param $date
     * @param $comment
     * @return bool
     */
    public function storeCentralIncome($name, $resellerId, $amount, $date, $comment)
    {
        $reseller = User::find($resellerId);

        $inc = new Income();
        $inc->gross_value = $amount;
        $inc->tax_value = $amount - ($amount / 1.27);
        $inc->name = $name;
        $inc->date = $date ? $date : date('Y-m-d');
        $inc->comment = $comment;
        $inc->save();
        Log::info(sprintf('Központ bevétele elmentve. (%s Ft, %s)', $amount, $name));

        if ($resellerId) {
            if (!$reseller) {
                Log::error('Nem található ilyen viszonteladó, ezért törlésre kerül a megadott bevétel.');

                try {
                    $inc->delete();
                } catch (Exception $e) {
                    Log::error('Hiba történt a bevétel törlésekor.');
                }

                return false;
            }

            // Létrehozzuk a kiadást neki
            return $this->storeResellerExpense($name, $amount, $reseller, $date ? $date : date('Y-m-d'), $comment);
        }

        return true;
    }

    /**
     * @param $name
     * @param $amount
     * @param User $reseller
     * @param $date
     * @param $comment
     * @return bool
     */
    public function storeResellerExpense($name, $amount, User $reseller, $date, $comment): bool
    {
        $expense = new Expense();
        $expense->gross_value = $amount;
        $expense->name = $name;
        $expense->user_id = $reseller ? $reseller->id : null;
        $expense->comment = $comment;
        if (($reseller && !$reseller->isAAM()) || !$reseller) {
            $expense->tax_value = $amount - ($amount / 1.27);
        }
        $expense->date = $date ? $date : date('Y-m-d');
        $expense->save();

        if ($reseller) {
            Log::info(sprintf('Viszonteladó kiadása elmentve. (%s, %s Ft, %s)', $reseller->name, $amount, $name));
        } else {
            Log::info(sprintf('Központ kiadása elmentve. (%s Ft, %s)', $amount, $name));
        }

        return true;
    }

    /**
     * @param $start
     * @param $end
     * @return array
     */
    public function getHqFinanceDaily($start, $end)
    {
        $sum = [
            'expense' => 0,
            'income' => 0,
            'tax' => 0,
        ];

        // Visszanyerjük a megfelelő lekérdezéssel a bevételeit és kiadásait
        /** @var Expense[] $expenses */
        $expenses = Expense::select(['id', 'name', 'gross_value', 'tax_value', 'comment', 'date'])->where([
            ['user_id', '=', null],
            ['date', '>=', $start],
            ['date', '<=', $end],
        ])->orderBy('date', 'DESC')
            ->get();
        /** @var Income[] $incoms */
        $incomes = Income::select(['id', 'name', 'gross_value', 'tax_value', 'comment', 'date'])->where([
            ['user_id', '=', null],
            ['date', '>=', $start],
            ['date', '<=', $end],
        ])->orderBy('date', 'DESC')
            ->get();

        /** @var Expense $expense */
        foreach ($expenses as $expense) {
            $sum['expense'] += $expense->gross_value;
        }

        /** @var Income $income */
        foreach ($incomes as $income) {
            $sum['income'] += $income->gross_value;
            $sum['tax'] += $income->tax_value;
        }

        $result = [
            'data' => [
                'expenses' => $expenses,
                'incomes' => $incomes,
            ],
            'sum' => $sum,
        ];

        $result['tableHTML'] = view('inc.finance.hq-daily')->with(['hqFinanceData' => $result])->toHtml();
        return $result;
    }

    public function getHqFinanceMonthly($start, $end)
    {
        return [];
    }

    /**
     * @param $amount
     * @param  User  $reseller
     * @return bool
     */
    public function addBalance($amount, User $reseller): bool
    {
        $reseller->balance += $amount;
        $saved = $reseller->save();
        Log::info(sprintf('Viszonteladó egyenlege feltöltve: %s Ft', $amount > 0 ? '+' . $amount : $amount));

        return $saved;
    }
}
