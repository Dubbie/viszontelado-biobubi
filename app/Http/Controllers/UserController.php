<?php

namespace App\Http\Controllers;

use App\Http\Requests\BillingoApiTestRequest;
use App\Post;
use App\Subesz\BillingoNewService;
use App\Subesz\OrderService;
use App\Subesz\RevenueService;
use App\User;
use App\UserDetails;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Psr\Http\Message\ResponseInterface;

class UserController extends Controller
{
    /**
     * UserController constructor.
     */
    public function __construct() {

    }

    /**
     * @return Factory|View
     */
    public function home() {
        /** @var RevenueService $revenueService */
        $revenueService = resolve('App\Subesz\RevenueService');
        /** @var BillingoNewService $billingoService */
        $billingoService = resolve('App\Subesz\BillingoNewService');

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now();

        // Benji kikeresése
        $benjiExpenses = null;
        $benji         = User::where('email', 'gbenji20@gmail.com')->first();
        if (Auth::user()->admin && $benji) {
            $benjiExpenses = $revenueService->getExpenseByRange($start, $end, $benji->id)['sum'];
        }

        $income  = $revenueService->getIncomeByRange($start, $end)['sum'];
        $expense = $revenueService->getExpenseByRange($start, $end, Auth::id())['sum'];
        // Benjit levonjuk ha kell
        if ($benjiExpenses) {
            $expense += $benjiExpenses;
        }
        $profit = $income - $expense;

        $billingoResults = $billingoService->isBillingoConnected(Auth::user());

        /** @var OrderService $os */
        $os = resolve('App\Subesz\OrderService');

        // Mai teendők
        $todos = Auth::user()->todos()->whereDate('deadline', Carbon::now())->where('completed_at', '=', null)->orderBy('deadline')->get();

        return view('home')->with([
            'orders'   => $os->getLatestOrder(5),
            'income'   => $income,
            'expense'  => $expense,
            'profit'   => $profit,
            'billingo' => $billingoResults,
            'news'     => Post::orderByDesc('created_at')->limit(5)->get(),
            'todos'    => $todos,
        ]);
    }

    /**
     * @return Factory|View
     */
    public function index() {
        $users = User::withCount(['deliveries', 'regions'])->get();

        return view('user.index')->with([
            'users' => $users,
        ]);
    }

    /**
     * @return Factory|View
     */
    public function create() {
        return view('user.create');
    }

    /**
     * @param           $userId
     * @param  Request  $request
     * @return Factory|View
     */
    public function show($userId, Request $request) {
        /** @var User $user */
        $user              = User::where('id', $userId)->withCount('deliveries')->first();
        $date              = $request->input('date') ?? null;
        $selectedReport    = null;
        $selectedMarketing = null;
        $active            = 'user-details';

        if ($date) {
            $carbonDate        = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $date.'-01 00:00:01');
            $selectedReport    = $user->reports()->whereDate('created_at', '=', $carbonDate->format('Y-m-d'))->first();
            $selectedMarketing = $user->marketingResults()->whereDate('date', '=', $carbonDate->format('Y-m-d'))->first();
            $active            = 'user-monthly-reports';
        } else {
            $selectedReport = $user->reports->last();

            if ($selectedReport) {
                $selectedMarketing = $user->marketingResults()->where('date', '=', $selectedReport->created_at->format('Y-m-d'))->first();
            }
        }

        return view('user.show')->with([
            'user'              => $user,
            'selectedReport'    => $selectedReport,
            'selectedMarketing' => $selectedMarketing,
            'activeTab'         => $active,
        ]);
    }

    /**
     * @param $userId
     * @return Factory|View
     */
    public function edit($userId) {
        $user = User::find($userId);

        return view('user.edit')->with([
            'user' => $user,
        ]);
    }

    /**
     * @param  Request  $request
     * @return RedirectResponse|Redirector
     */
    public function store(Request $request) {
        $data = $request->validate([
            'u-name'             => 'required',
            'u-email'            => 'required|email|unique:users,email',
            'u-password'         => 'required',
            'u-aam'              => 'nullable',
            'u-billingo-api-key' => 'nullable',
            'u-block-uid'        => 'nullable',
        ]);

        $user                   = new User();
        $user->name             = trim($data['u-name']);
        $user->email            = trim($data['u-email']);
        $user->password         = Hash::make($data['u-password']);
        $user->vat_id           = array_key_exists('u-aam', $data) ? env('AAM_VAT_ID') : 1;
        $user->billingo_api_key = array_key_exists('u-billingo-api-key', $data) ? $data['u-billingo-api-key'] : null;
        $user->block_uid        = array_key_exists('u-block-uid', $data) ? $data['u-block-uid'] : null;

        if (! $user->save()) {
            Log::error('Hiba történt a felhasználó mentésekor! %s', $user);

            return redirect(url()->previous())->withErrors([
                'store' => 'Hiba történt a felhasználó létrehozásakor!',
            ]);
        }

        return redirect(action('UserController@index'))->with([
            'success' => 'Új felhasználó sikeresen létrehozva!',
        ]);
    }

    /**
     * @param           $userId
     * @param  Request  $request
     * @return RedirectResponse|Redirector
     */
    public function update($userId, Request $request) {
        $data = $request->validate([
            'u-name'                   => 'required',
            'u-email'                  => 'required|email|unique:users,email,'.$userId,
            'u-aam'                    => 'nullable',
            'u-billingo-api-key'       => 'nullable',
            'u-block-uid'              => 'nullable',
            'u-billing-name'           => 'nullable',
            'u-billing-zip'            => 'nullable',
            'u-billing-city'           => 'required_with:u-billing-zip|nullable',
            'u-billing-address1'       => 'required_with:u-billing-zip|nullable',
            'u-billing-address2'       => 'nullable',
            'u-billing-tax-number'     => 'nullable',
            'u-billing-account-number' => 'nullable',
            'u-shipping-name'          => 'nullable',
            'u-shipping-email'         => 'nullable',
            'u-shipping-phone'         => 'nullable',
            'u-shipping-zip'           => 'nullable',
            'u-shipping-city'          => 'required_with:u-shipping-zip|nullable',
            'u-shipping-address1'      => 'required_with:u-shipping-zip|nullable',
            'u-shipping-address2'      => 'nullable',
            'u-marketing-balance'      => 'required',
            'u-email-notifications'    => 'nullable',
        ]);

        $user                      = User::find($userId);
        $user->name                = $data['u-name'];
        $user->email               = $data['u-email'];
        $user->vat_id              = array_key_exists('u-aam', $data) ? env('AAM_VAT_ID') : 1;
        $user->billingo_api_key    = array_key_exists('u-billingo-api-key', $data) ? $data['u-billingo-api-key'] : $user->billingo_api_key;
        $user->block_uid           = array_key_exists('u-block-uid', $data) ? $data['u-block-uid'] : $user->block_uid;
        $user->balance             = array_key_exists('u-marketing-balance', $data) ? (double) $data['u-marketing-balance'] : $user->balance;
        $user->email_notifications = array_key_exists('u-email-notifications', $data) && $data['u-email-notifications'] == 'on';

        $detailsKeys       = [
            'u-billing-name',
            'u-billing-zip',
            'u-billing-city',
            'u-billing-address1',
            'u-billing-address2',
            'u-billing-tax-number',
            'u-billing-account-number',
            'u-shipping-name',
            'u-shipping-email',
            'u-shipping-phone',
            'u-shipping-zip',
            'u-shipping-city',
            'u-shipping-address1',
            'u-shipping-address2',
        ];
        $shouldHaveDetails = false;
        foreach ($detailsKeys as $key) {
            if (strlen($data[$key]) > 0) {
                $shouldHaveDetails = true;
                break;
            }
        }

        // Megnézzük, hogy kell-e foglalkozni a részletekkel
        if ($shouldHaveDetails) {
            $ud = $user->details;
            $as = resolve('App\Subesz\AddressService');

            if (! $ud) {
                $ud          = new UserDetails();
                $ud->user_id = $user->id;
            }

            // Mentsük el a címeket
            $billingAddress  = $as->storeAddress($data['u-billing-zip'], $data['u-billing-city'], $data['u-billing-address1'], $data['u-billing-address2']);
            $shippingAddress = $as->storeAddress($data['u-shipping-zip'], $data['u-shipping-city'], $data['u-shipping-address1'], $data['u-shipping-address2']);

            // Mentsük el az egyéb adatokat
            $ud->billing_name           = $data['u-billing-name'];
            $ud->billing_tax_number     = $data['u-billing-tax-number'];
            $ud->billing_account_number = $data['u-billing-account-number'];
            $ud->shipping_email         = $data['u-shipping-email'];
            $ud->shipping_phone         = $data['u-shipping-phone'];
            $ud->billing_address_id     = $billingAddress ? $billingAddress->id : null;
            $ud->shipping_address_id    = $shippingAddress ? $shippingAddress->id : null;

            $ud->save();
        } else {
            if ($user->details) {
                try {
                    $user->details->delete();
                } catch (Exception $e) {
                    Log::error('Hiba történt a felhasználó részleteinek törlésekor');
                    Log::error($e->getMessage());
                }
            }
        }

        if ($user->save()) {
            return redirect(url()->previous())->with([
                'success' => 'Felhasználó sikeresen frissítve!',
            ]);
        } else {
            return redirect(url()->previous())->withErrors([
                'store' => 'Hiba történt a felhasználó frissítésekor!',
            ]);
        }
    }

    /**
     * @return Factory|View
     */
    public function profile() {
        /** @var BillingoNewService $billingoService */
        $billingoService = resolve('App\Subesz\BillingoNewService');
        $billingoResults = $billingoService->isBillingoConnected(Auth::user());

        return view('profile')->with([
            'billingo' => $billingoResults,
        ]);
    }

    /**
     * @param  Request  $request
     * @return RedirectResponse|Redirector
     */
    public function updatePassword(Request $request) {
        $data = $request->validate([
            'old-password' => 'required',
            'password'     => 'required|confirmed',
        ]);

        // Megnézzük, hogy jó-e a jelszó
        $user = User::find(Auth::id());
        if (! Hash::check($data['old-password'], $user->password)) {
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
     * @param  BillingoApiTestRequest  $request
     * @return mixed|ResponseInterface
     */
    public function testBillingo(BillingoApiTestRequest $request) {
        /** @var BillingoNewService $billingoService */
        $billingoService = resolve('App\Subesz\BillingoNewService');
        $data            = $request->validated();
        $response        = $billingoService->isBillingoWorking($data['u-billingo-api-key'], $data['u-block-uid']);

        return $response;
    }

    /**
     * @param $thisWeek
     * @param $lastWeek
     * @return string
     */
    private function getDiffPercent($thisWeek, $lastWeek) {
        if ($lastWeek == 0 && $thisWeek == 0) {
            $amount = 0;
        } else {
            if ($lastWeek == 0) {
                $amount = (100 - round(($lastWeek / $thisWeek) * 100));
            } else {
                $amount = -1 * (100 - round(($thisWeek / $lastWeek) * 100));
            }
        }

        if ($amount > 0) {
            $amount = '+'.$amount;
        }

        return $amount.'%';
    }
}
