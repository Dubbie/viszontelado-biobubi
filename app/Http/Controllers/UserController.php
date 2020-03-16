<?php

namespace App\Http\Controllers;

use App\Subesz\OrderService;
use App\User;
use App\UserZip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /** @var OrderService */
    private $orderService;

    /**
     * UserController constructor.
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
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
            'u-zip' => 'required',
        ]);

        $user = User::find($userId);
        $user->name = $data['u-name'];
        $user->email = $data['u-email'];

        // Kitöröljük a régieket...
        UserZip::where('user_id', $userId)->delete();

        // Bejönnek az újak...
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

    public function orders($userId) {
        $orders = $this->orderService->getOrdersByUserId($userId);

        return view('order.index')->with([
            'orders' => $orders
        ]);
    }
}
