<?php

namespace App\Http\Controllers;

use App\Expense;
use App\Income;
use App\Subesz\OrderService;
use App\Subesz\RevenueService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RevenueController extends Controller
{
    /** @var OrderService */
    private $orderService;

    /** @var RevenueService */
    private $revenueService;

    /**
     * RevenueController constructor.
     * @param OrderService $orderService
     * @param RevenueService $revenueService
     */
    public function __construct(OrderService $orderService, RevenueService $revenueService)
    {
        $this->orderService = $orderService;
        $this->revenueService = $revenueService;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function income()
    {
        return view('revenue.income');
    }

    /**
     * @param Request $request
     * @return array
     */
    public function fetchIncome(Request $request)
    {
        $input = $request->validate([
            'start-date' => 'required',
            'end-date' => 'required',
        ]);

        $dateFormat = 'M. d.';
        $apiResults = $this->revenueService->getIncomeByRange(
                Carbon::parse($input['start-date']),
                Carbon::parse($input['end-date'] . ' 23:59:59')
        );

        $labels = [];
        $data = [];
        $count = [];
        foreach ($apiResults['data'] as $stat) {
            $date = Carbon::parse($stat['date']);
            $labels[] = $date->format($dateFormat);

            $data[] = [
                'x' => $date->format($dateFormat),
                'y' => $stat['total'],
            ];

            $count[] = [
                'date' => $date->format($dateFormat),
                'count' => $stat['count'],
            ];
        }

        $response = [
            'labels' => $labels,
            'data' => $data,
            'count' => $count,
            'sum' => $apiResults['sum'],
        ];

        return $response;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function expense()
    {
        return view('revenue.expense');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function storeExpense(Request $request)
    {
        $data = $request->validate([
            'e-hq' => 'nullable',
            'e-name' => 'required',
            'e-amount' => 'required',
            'e-date' => 'required',
            'e-comment' => 'nullable',
        ]);

        if (array_key_exists('e-hq', $data) && $data['e-hq'] == true) {
            $this->revenueService->storeResellerExpense($data['e-name'], intval($data['e-amount']), null, date('Y-m-d H:i:s', strtotime($data['e-date'])), $data['e-comment']);
        } else {
            $this->revenueService->storeResellerExpense($data['e-name'], intval($data['e-amount']), Auth::user(), date('Y-m-d H:i:s', strtotime($data['e-date'])), $data['e-comment']);
        }

        return redirect(url()->previous())->with([
            'success' => 'Kiadás sikeresen hozzáadva',
        ]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function fetchExpense(Request $request)
    {
        $input = $request->validate([
            'start-date' => 'required',
            'end-date' => 'required',
        ]);

        $userExpenses = $this->revenueService->getExpenseByRange(
            Carbon::parse($input['start-date']),
            Carbon::parse($input['end-date'] . ' 23:59:59'),
            Auth::id()
        );

        return $userExpenses;
    }

    /**
     * @param $expenseId
     * @return array
     */
    public function destroyExpense($expenseId) {
        $expense = Auth::user()->expenses->find(intval($expenseId));
        $success = false;

        if ($expense) {
            try {
                $expense->delete();
            } catch (\Exception $e) {
                \Log::error('Hiba történt a kiadás törlésekor...');
                \Log::error($e->getMessage());
            }
            $success = true;
        }

        return [
            'success' => $success,
        ];
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function hqFinance() {
        return view('hq.finance')->with([
            'hqFinanceData' => $this->revenueService->getHqFinanceDaily(Carbon::now()->firstOfMonth(), Carbon::now()->endOfDay()),
        ]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getHqFinance(Request $request) {
        $input = $request->validate([
            'view-mode' => 'required',
            'start-date' => 'required',
            'end-date' => 'required',
        ]);

        $hqFinance = $this->revenueService->getHqFinanceDaily(
            Carbon::parse($input['start-date']),
            Carbon::parse($input['end-date'] . ' 23:59:59')
        );
        if ($input['view-mode'] != 'DAILY') {
            $hqFinance = $this->revenueService->getHqFinanceMonthly(
                Carbon::parse($input['start-date']),
                Carbon::parse($input['end-date'] . ' 23:59:59')
            );
        }

        return $hqFinance;
    }
}