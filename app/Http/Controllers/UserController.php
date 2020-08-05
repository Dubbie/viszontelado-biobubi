<?php

namespace App\Http\Controllers;

use App\Http\Requests\BillingoApiTestRequest;
use App\Post;
use App\Subesz\BillingoNewService;
use App\Subesz\OrderService;
use App\Subesz\RevenueService;
use App\User;
use App\UserZip;
use Billingo\API\Connector\HTTP\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    /**
     * UserController constructor.
     */
    public function __construct()
    {

    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function home()
    {
//        /** @var BillingoNewService $bns */
//        $bns = resolve('App\Subesz\BillingoNewService');
        /** @var RevenueService $revenueService */
        $revenueService = resolve('App\Subesz\RevenueService');
//        /** @var BillingoNewService $billingoService */
        $billingoService = resolve('App\Subesz\BillingoNewService');

        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now();

        // Benji kikeresése
        $benjiExpenses = null;
        $benji = User::where('email', 'gbenji20@gmail.com')->first();
        if (Auth::user()->admin && $benji) {
            $benjiExpenses = $revenueService->getExpenseByRange($start, $end, $benji->id)['sum'];
        }

        $income = $revenueService->getIncomeByRange($start, $end)['sum'];
        $expense = $revenueService->getExpenseByRange($start, $end, Auth::id())['sum'];
        // Benjit levonjuk ha kell
        if ($benjiExpenses) {
            $expense += $benjiExpenses;
        }
        $profit = $income - $expense;

        $billingoResults = $billingoService->isBillingoConnected(Auth::user());

        /** @var OrderService $os */
        $os = resolve('App\Subesz\OrderService');

        return view('home')->with([
            'orders' => $os->getLatestOrder(5),
            'income' => $income,
            'expense' => $expense,
            'profit' => $profit,
            'billingo' => $billingoResults,
            'news' => Post::orderByDesc('created_at')->limit(5)->get(),
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $users = User::withCount(['deliveries', 'zips'])->get();

        return view('user.index')->with([
            'users' => $users,
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
            'u-aam' => 'nullable',
            'u-billingo-api-key' => 'nullable',
            'u-block-uid' => 'nullable',
        ]);

        $user = new User();
        $user->name = trim($data['u-name']);
        $user->email = trim($data['u-email']);
        $user->password = Hash::make($data['u-password']);
        $user->vat_id = array_key_exists('u-aam', $data) ? env('AAM_VAT_ID') : 1;
        $user->billingo_api_key = array_key_exists('u-billingo-api-key', $data) ? $data['u-billingo-api-key'] : null;
        $user->block_uid = array_key_exists('u-block-uid', $data) ? $data['u-block-uid'] : null;

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
            'u-billingo-public-key' => 'nullable',
            'u-billingo-private-key' => 'nullable',
            'u-block-uid' => 'nullable',
        ]);

        $user = User::find($userId);
        $user->name = $data['u-name'];
        $user->email = $data['u-email'];
        $user->vat_id = array_key_exists('u-aam', $data) ? env('AAM_VAT_ID') : 1;
        $user->billingo_api_key = array_key_exists('u-billingo-api-key', $data) ? $data['u-billingo-api-key'] : $user->billingo_api_key;
        $user->block_uid = array_key_exists('u-block-uid', $data) ? $data['u-block-uid'] : $user->block_uid;

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
        /** @var BillingoNewService $billingoService */
        $billingoService = resolve('App\Subesz\BillingoNewService');
        $billingoResults = $billingoService->isBillingoConnected(Auth::user());

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
        /** @var BillingoNewService $billingoService */
        $billingoService = resolve('App\Subesz\BillingoNewService');
        $data = $request->validated();
        $response = $billingoService->isBillingoWorking($data['u-billingo-api-key'], $data['u-block-uid']);
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
