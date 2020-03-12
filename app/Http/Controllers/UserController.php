<?php

namespace App\Http\Controllers;

use App\User;
use App\UserZip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
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
        }

        $zips = json_decode($data['u-zip']);
        $zipSuccess = 0;
        foreach ($zips as $i => $zip) {
            $userZip = new UserZip();
            $userZip->user_id = $user->id;
            $userZip->zip = $zip->value;

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
}
