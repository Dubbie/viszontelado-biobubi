<?php

namespace App\Http\Controllers;

use App\Http\Requests\BillingoApiTestRequest;
use App\Subesz\BillingoService;
use App\Subesz\OrderService;
use App\Subesz\RevenueService;
use App\User;
use App\UserZip;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /** @var OrderService */
    private $orderService;

    /** @var RevenueService */
    private $revenueService;

    /** @var BillingoService */
    private $billingoService;

    /**
     * UserController constructor.
     * @param OrderService $orderService
     * @param RevenueService $revenueService
     * @param BillingoService $billingoService
     */
    public function __construct(OrderService $orderService, RevenueService $revenueService, BillingoService $billingoService)
    {
        $this->orderService = $orderService;
        $this->revenueService = $revenueService;
        $this->billingoService = $billingoService;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function home()
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now();

        $income = $this->revenueService->getIncomeByRange($start, $end)['sum'];
        $expense = $this->revenueService->getExpenseByRange($start, $end, Auth::id())['sum'];
        $profit = $income - $expense;
        $billingoResults = $this->billingoService->getBlockByUid(Auth::user()->billingo_public_key, Auth::user()->billingo_private_key, Auth::user()->block_uid);

        return view('home')->with([
            'orders' => $this->orderService->getLatestOrder(5),
            'income' => $income,
            'expense' => $expense,
            'profit' => $profit,
            'billingo' => $billingoResults,
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('user.index')->with([
            'users' => User::all(),
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('user.create');
    }

    /**
     * @param $userId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($userId)
    {
        return view('inc.user-details-content')->with([
            'user' => User::find($userId),
        ]);
    }

    /**
     * @param $userId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($userId)
    {
        $user = User::find($userId);
        $zips = [];

        foreach ($user->zips as $zip) {
            $zips[] = [
                'value' => $zip->zip,
            ];
        }

        return view('user.edit')->with([
            'user' => $user,
            'zips' => json_encode($zips),
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'u-name' => 'required',
            'u-email' => 'required|email|unique:users,email',
            'u-password' => 'required',
            'u-zip' => 'required',
            'u-aam' => 'nullable'
        ]);

        $user = new User();
        $user->name = trim($data['u-name']);
        $user->email = trim($data['u-email']);
        $user->password = Hash::make($data['u-password']);
        $user->vat_id = array_key_exists('u-aam', $data) ? env('AAM_VAT_ID') : 1;

        if (!$user->save()) {
            Log::error('Hiba történt a felhasználó mentésekor! %s', $user);
            return redirect(url()->previous())->withErrors([
                'store' => 'Hiba történt a felhasználó létrehozásakor!',
            ]);
        }

        $zips = json_decode($data['u-zip'], true);
        $zipSuccess = 0;
        foreach ($zips as $i => $zip) {

            $userZip = new UserZip();
            $userZip->user_id = $user->id;
            $userZip->zip = $zip['value'];

            if ($userZip->save()) {
                $zipSuccess++;
            }
        }

        if ($zipSuccess == count($zips)) {
            return redirect(action('UserController@index'))->with([
                'success' => 'Új felhasználó sikeresen létrehozva!',
            ]);
        } else {
            return redirect(url()->previous())->withErrors([
                'store' => 'Hiba történt a felhasználó létrehozásakor!',
            ]);
        }
    }

    /**
     * @param $userId
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update($userId, Request $request)
    {
        $data = $request->validate([
            'u-name' => 'required',
            'u-email' => 'required|email|unique:users,email,' . $userId,
            'u-zip' => 'nullable',
            'u-aam' => 'nullable',
        ]);

        $user = User::find($userId);
        $user->name = $data['u-name'];
        $user->email = $data['u-email'];
        $user->vat_id = array_key_exists('u-aam', $data) ? env('AAM_VAT_ID') : 1;

        // Kitöröljük a régieket...
        UserZip::where('user_id', $userId)->delete();

        // Bejönnek az újak...
        $zips = $data['u-zip'] ? json_decode($data['u-zip'], true) : [];
        $zipSuccess = 0;
        foreach ($zips as $i => $zip) {
            $userZip = new UserZip();
            $userZip->user_id = $user->id;
            $userZip->zip = $zip['value'];

            if ($userZip->save()) {
                $zipSuccess++;
            }
        }

        if ($zipSuccess == count($zips)) {
            $user->save();

            return redirect(action('UserController@index'))->with([
                'success' => 'Felhasználó sikeresen frissítve!',
            ]);
        } else {
            return redirect(url()->previous())->withErrors([
                'store' => 'Hiba történt a felhasználó frissítésekor!',
            ]);
        }
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function profile()
    {
        $billingoResults = $this->billingoService->getBlockByUid(Auth::user()->billingo_public_key, Auth::user()->billingo_private_key, Auth::user()->block_uid);

        return view('profile')->with([
            'billingo' => $billingoResults,
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'old-password' => 'required',
            'password' => 'required|confirmed'
        ]);

        // Megnézzük, hogy jó-e a jelszó
        $user = User::find(Auth::id());
        if (!Hash::check($data['old-password'], $user->password)) {
            return redirect(url()->previous())->with([
                'error' => 'Helytelen jelenlegi jelszó lett megadva',
            ]);
        }

        // Frissítsük az újra
        $user->password = Hash::make($data['password']);
        if ($user->save()) {
            return redirect(url()->previous())->with([
                'success' => 'Jelszó sikeresen frissítve',
            ]);
        }

        return redirect(url()->previous())->with([
            'error' => 'Hiba történt a jelszavának frissítésekor',
        ]);
    }

    /**
     * @param BillingoApiTestRequest $request
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function testBillingo(BillingoApiTestRequest $request)
    {
        $data = $request->validated();

        $response = $this->billingoService->getBlockByUid($data['u-billingo-public-key'], $data['u-billingo-private-key'], $data['u-block-uid']);
        return $response;
    }

    /**
     * @param $thisWeek
     * @param $lastWeek
     * @return string
     */
    private function getDiffPercent($thisWeek, $lastWeek)
    {
        if ($lastWeek == 0 && $thisWeek == 0) {
            $amount = 0;
        } else if ($lastWeek == 0) {
            $amount = (100 - round(($lastWeek / $thisWeek) * 100));
        } else {
            $amount = -1 * (100 - round(($thisWeek / $lastWeek) * 100));
        }

        if ($amount > 0) {
            $amount = '+' . $amount;
        }

        return $amount . '%';
    }
}
