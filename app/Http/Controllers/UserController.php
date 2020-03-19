<?php

namespace App\Http\Controllers;

use App\Subesz\OrderService;
use App\Subesz\RevenueService;
use App\User;
use App\UserZip;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /** @var OrderService */
    private $orderService;

    /** @var RevenueService */
    private $revenueService;

    /**
     * UserController constructor.
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
    public function home() {
        $start = Carbon::now()->subDays(7);
        $end = Carbon::now();

        $income = $this->revenueService->getIncomeByRange($start, $end)['sum'];
        $expense = $this->revenueService->getExpenseByRange($start, $end, Auth::id())['sum'];
        $profit = $income - $expense;

        return view('home')->with([
            'order' => $this->orderService->getLatestOrder(),
            'income' => $income,
            'expense' => $expense,
            'profit' => $profit,
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index() {
        return view('user.index')->with([
            'users' => User::all(),
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create() {
        return view('user.create');
    }

    /**
     * @param $userId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($userId) {
        return view('inc.user-details-content')->with([
            'user' => User::find($userId),
        ]);
    }

    /**
     * @param $userId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($userId) {
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
    public function store(Request $request) {
        $data = $request->validate([
            'u-name' => 'required',
            'u-email' => 'required|email|unique:users,email',
            'u-password' => 'required',
            'u-zip' => 'required',
        ]);

        $user = new User();
        $user->name = trim($data['u-name']);
        $user->email = trim($data['u-email']);
        $user->password = Hash::make($data['u-password']);

        if (!$user->save()) {
            Log::error('Hiba történt a felhasználó mentésekor! %s', $user);
            return redirect(url()->previous())->withErrors([
                'store' => 'Hiba történt a felhasználó létrehozásakor!',
            ]);
        }

        $zips = json_decode($data['u-zip'], true);
        $zipSuccess = 0;
        foreach ($zips as $i => $zip) {
            Validator::make($zip, [
                'value' => 'unique:user_zips,zip'
            ], [
                'value.unique' => 'Az irányítószám (:input) már foglalt!',
            ])->validate();

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
    public function update($userId, Request $request) {
        $data = $request->validate([
            'u-name' => 'required',
            'u-email' => 'required|email|unique:users,email,' . $userId,
            'u-zip' => 'nullable',
        ]);

        $user = User::find($userId);
        $user->name = $data['u-name'];
        $user->email = $data['u-email'];

        // Kitöröljük a régieket...
        UserZip::where('user_id', $userId)->delete();

        // Bejönnek az újak...
        $zips = $data['u-zip'] ? json_decode($data['u-zip'], true) : [];
        $zipSuccess = 0;
        foreach ($zips as $i => $zip) {
            Validator::make($zip, [
                'value' => 'unique:user_zips,zip'
            ], [
                'value.unique' => 'Az irányítószám (:input) már foglalt!',
            ])->validate();

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
    public function profile() {
        return view('profile');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updatePassword(Request $request) {
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
}
