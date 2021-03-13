<?php

namespace App\Http\Controllers;

use App\MoneyTransfer;
use App\Order;
use App\Subesz\OrderService;
use App\Subesz\TransferService;
use App\User;
use Auth;
use Illuminate\Http\Request;

class MoneyTransferController extends Controller
{
    /** @var \App\Subesz\OrderService */
    private $orderService;

    /** @var \App\Subesz\TransferService */
    private $transferService;

    /** @var string */
    private $sessionResellerKey;

    /** @var string */
    private $sessionOrderIdsKey;

    /**
     * MoneyTransferController constructor.
     *
     * @param  \App\Subesz\OrderService     $orderService
     * @param  \App\Subesz\TransferService  $transferService
     */
    public function __construct(OrderService $orderService, TransferService $transferService) {
        $this->orderService       = $orderService;
        $this->transferService    = $transferService;
        $this->sessionResellerKey = 'transfer-reseller-id';
        $this->sessionOrderIdsKey = 'transfer-order-ids';
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index() {
        return view('hq.transfers.index')->with([
            'transfers' => MoneyTransfer::withCount('transferOrders')->get(),
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function chooseReseller() {
        return view('hq.transfers.reseller')->with([
            'resellers' => User::whereHas('zips')->where('id', '!=', Auth::id())->get(),
        ]);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function storeReseller(Request $request) {
        $data = $request->validate([
            'mt-reseller-id' => 'required|numeric',
        ]);

        // Elrakjuk sessionbe
        session()->put($this->sessionResellerKey, intval($data['mt-reseller-id']));

        return redirect(action('MoneyTransferController@chooseOrders'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function chooseOrders() {
        $resellerId = session()->has($this->sessionResellerKey) ? intval(session()->get($this->sessionResellerKey)) : null;

        if ($resellerId === null) {
            return redirect(action('MoneyTransferController@chooseReseller'))->with([
                'error' => 'Kérlek válassz egy viszonteladót',
            ]);
        }

        return view('hq.transfers.orders')->with([
            'orders' => $this->orderService->getBankcardOrdersByResellerId($resellerId),
        ]);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function storeOrders(Request $request) {
        $data = $request->validate([
            'mt-order-id' => 'required|array',
        ]);

        // Elrakjuk sessionbe
        session()->put($this->sessionOrderIdsKey, $data['mt-order-id']);

        return redirect(action('MoneyTransferController@create'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create() {
        if (session()->get($this->sessionResellerKey) === null) {
            return redirect(action('MoneyTransferController@chooseReseller'))->with([
                'error' => 'Kérlek válassz egy viszonteladót',
            ]);
        }

        if (session()->get($this->sessionOrderIdsKey) === null) {
            return redirect(action('MoneyTransferController@chooseOrders'))->with([
                'error' => 'Kérlek válassz megrendeléseket',
            ]);
        }

        $sum = Order::whereIn('id', session()->get($this->sessionOrderIdsKey))->sum('total_gross');

        return view('hq.transfers.create')->with([
            'reseller' => User::find(session()->get($this->sessionResellerKey)),
            'orders'   => Order::whereIn('id', session()->get($this->sessionOrderIdsKey))->get(),
            'sum'      => $sum,
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store() {
        $resellerId = session()->get($this->sessionResellerKey) ?? null;
        $orderIds   = session()->get($this->sessionOrderIdsKey) ?? null;
        $sum        = null;

        if ($resellerId === null) {
            return redirect(action('MoneyTransferController@chooseReseller'))->with([
                'error' => 'Kérlek válassz egy viszonteladót',
            ]);
        }

        if ($orderIds === null) {
            return redirect(action('MoneyTransferController@chooseOrders'))->with([
                'error' => 'Kérlek válassz megrendeléseket',
            ]);
        } else {
            $sum = Order::whereIn('id', $orderIds)->sum('total_gross');
        }

        // Voltak megrendelések, számíthatunk összegre, majd elmentjük az átutalást
        $response = $this->transferService->storeTransfer($resellerId, $orderIds, $sum);

        // Hiba esetén visszairányítjuk valahova, jó esetben az előző URL-re, de egyébként az első lépésre
        if (! $response['success']) {
            return redirect(url()->previous(action('MoneyTransferController@chooseReseller')))->with([
                'errors' => $response['message'],
            ]);
        }

        // Kitöröljük session-ből amiket elmentettünk
        session()->remove($this->sessionResellerKey);
        session()->remove($this->sessionOrderIdsKey);

        // Végeztünk
        return redirect(action('MoneyTransferController@index'))->with([
            'success' => $response['message'],
        ]);
    }
}
