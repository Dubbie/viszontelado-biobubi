<?php

namespace App\Http\Controllers;

use App\Expense;
use App\Subesz\OrderService;
use App\Subesz\RevenueService;
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
                Carbon::parse($input['end-date'])
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
            'e-name' => 'required',
            'e-amount' => 'required',
            'e-date' => 'required',
        ]);

        $expense = new Expense();
        $expense->name = $data['e-name'];
        $expense->amount = intval($data['e-amount']);
        $expense->date = date('Y-m-d H:i:s', strtotime($data['e-date']));
        $expense->user_id = Auth::id();
        $expense->save();

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

        return $this->revenueService->getExpenseByRange(
            Carbon::parse($input['start-date']),
            Carbon::parse($input['end-date']),
            Auth::id()
        );
    }
}
