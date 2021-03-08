<?php

namespace App\Http\Controllers;

use App\Expense;
use App\Income;
use App\Order;
use App\Subesz\OrderService;
use App\Subesz\RevenueService;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Log;

class RevenueController extends Controller
{
    /** @var OrderService */
    private $orderService;

    /** @var RevenueService */
    private $revenueService;

    /**
     * RevenueController constructor.
     *
     * @param OrderService   $orderService
     * @param RevenueService $revenueService
     */
    public function __construct(OrderService $orderService, RevenueService $revenueService)
    {
        $this->orderService = $orderService;
        $this->revenueService = $revenueService;
    }

    /**
     * @return Factory|View
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
            'user-id'    => 'nullable',
            'start-date' => 'required',
            'end-date'   => 'required',
        ]);

        $dateFormat = 'M. d.';
        $apiResults = $this->revenueService->getIncomeByRange(Carbon::parse($input['start-date']), Carbon::parse($input['end-date'].' 23:59:59'), $input['user-id'] ?? null);

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
                'date'  => $date->format($dateFormat),
                'count' => $stat['count'],
            ];
        }

        $response = [
            'labels' => $labels,
            'data'   => $data,
            'count'  => $count,
            'sum'    => $apiResults['sum'],
        ];

        return $response;
    }

    /**
     * @return Factory|View
     */
    public function expense()
    {
        return view('revenue.expense');
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Redirector
     */
    public function storeExpense(Request $request)
    {
        $data = $request->validate([
            'e-hq'      => 'nullable',
            'e-name'    => 'required',
            'e-amount'  => 'required',
            'e-date'    => 'required',
            'e-comment' => 'nullable',
        ]);

        if (array_key_exists('e-hq', $data) && $data['e-hq'] == true) {
            $this->revenueService->storeResellerExpense($data['e-name'], intval($data['e-amount']), null, date('Y-m-d H:i:s', strtotime($data['e-date'])), $data['e-comment'] ?? null);
        } else {
            $this->revenueService->storeResellerExpense($data['e-name'], intval($data['e-amount']), Auth::user(), date('Y-m-d H:i:s', strtotime($data['e-date'])), $data['e-comment'] ?? null);
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
            'user-id'    => 'nullable',
            'start-date' => 'required',
            'end-date'   => 'required',
        ]);

        $userExpenses = $this->revenueService->getExpenseByRange(Carbon::parse($input['start-date']), Carbon::parse($input['end-date'].' 23:59:59'), $input['user-id'] ?? Auth::id());

        return $userExpenses;
    }

    /**
     * @param $expenseId
     * @return array
     */
    public function destroyExpense($expenseId)
    {
        $expense = Auth::user()->expenses->find(intval($expenseId));
        $success = false;

        if ($expense) {
            try {
                $expense->delete();
            } catch (Exception $e) {
                Log::error('Hiba történt a kiadás törlésekor...');
                Log::error($e->getMessage());
            }
            $success = true;
        }

        return [
            'success' => $success,
        ];
    }

    /**
     * @return Factory|View
     */
    public function hqFinance()
    {
        return view('hq.finance')->with([
            'resellers'     => User::where('admin', false)->orderBy('name')->get(),
            'hqFinanceData' => $this->revenueService->getHqFinanceDaily(Carbon::now()->firstOfMonth(), Carbon::now()->endOfDay()),
        ]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getHqFinance(Request $request)
    {
        $input = $request->validate([
            'view-mode'  => 'required',
            'start-date' => 'required',
            'end-date'   => 'required',
        ]);

        $hqFinance = $this->revenueService->getHqFinanceDaily(Carbon::parse($input['start-date']), Carbon::parse($input['end-date'].' 23:59:59'));
        if ($input['view-mode'] != 'DAILY') {
            $hqFinance = $this->revenueService->getHqFinanceMonthly(Carbon::parse($input['start-date']), Carbon::parse($input['end-date'].' 23:59:59'));
        }

        return $hqFinance;
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Redirector
     */
    public function storeIncome(Request $request)
    {
        $data = $request->validate([
            'hqi-name'        => 'required',
            'hqi-amount'      => 'required',
            'hqi-reseller-id' => 'nullable',
            'hqi-date'        => 'required',
            'hqi-comment'     => 'nullable',
        ]);

        $this->revenueService->storeCentralIncome($data['hqi-name'], $data['hqi-reseller-id'] ?? null, intval($data['hqi-amount']), date('Y-m-d H:i:s', strtotime($data['hqi-date'])), $data['hqi-comment'] ?? null);

        return redirect(url()->previous())->with([
            'success' => 'Bevétel sikeresen hozzáadva',
        ]);
    }

    /**
     * Frissíti a megrendelésekhez tartozó bevételeket
     */
    public function generateOrderIncomes() {
        /** @var Order $order */
        foreach (Order::all() as $order) {
            $order->updateIncome();
        }
    }
}
