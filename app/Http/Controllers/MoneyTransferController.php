<?php

namespace App\Http\Controllers;

use App\MoneyTransfer;
use App\Order;
use App\Subesz\OrderService;
use App\Subesz\TransferService;
use App\Subesz\UserService;
use App\User;
use Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Log;
use Storage;

/**
 * Class MoneyTransferController
 *
 * @package App\Http\Controllers
 */
class MoneyTransferController extends Controller
{
    /** @var \App\Subesz\UserService */
    private $userService;

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
     * @param  \App\Subesz\UserService      $userService
     */
    public function __construct(
        OrderService $orderService,
        TransferService $transferService,
        UserService $userService
    ) {
        $this->orderService       = $orderService;
        $this->transferService    = $transferService;
        $this->userService        = $userService;
        $this->sessionResellerKey = 'transfer-reseller-id';
        $this->sessionOrderIdsKey = 'transfer-order-ids';
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request) {
        $transfers = $this->transferService->getTransfersQueryByUser(Auth::id())->withCount('transferOrders');
        $resellers = Auth::user()->admin ? $this->userService->getResellers() : [];
        $filter    = [];

        // Tartalmazza
        if ($request->has('filter-contains')) {
            $filter['contains'] = $request->input('filter-contains');
            $transfers          = $transfers->contains($request->input('filter-contains'));
        }

        // Állapot
        if ($request->has('filter-status')) {
            $filter['status'] = $request->input('filter-status');

            if ($filter['status'] == 'true') {
                $transfers = $transfers->completed();
            } else {
                if ($filter['status'] == 'false') {
                    $transfers = $transfers->incomplete();
                }
            }
        }

        // Viszonteladó
        if ($request->has('filter-reseller') && Auth::user()->admin) {
            $filter['reseller'] = $request->input('filter-reseller');

            if ($filter['reseller'] != 'ALL') {
                $transfers = $transfers->where('user_id', $filter['reseller']);
            }
        }

        $transfers = $transfers->orderBy('completed_at')->orderBy('created_at')->paginate(25);

        return view('hq.transfers.index')->with([
            'transfers' => $transfers,
            'resellers' => $resellers,
            'filter'    => $filter,
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function chooseReseller() {
        return view('hq.transfers.reseller')->with([
            'resellers' => $this->userService->getResellers(),
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
            'reseller' => User::find($resellerId),
            'orders'   => $this->orderService->getBankcardOrdersByResellerId($resellerId),
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

    /**
     * @param $transferId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($transferId) {
        $mt = $this->transferService->getTransfersQueryByUser(Auth::id())->find($transferId);

        return view('hq.transfers.show')->with([
            'transfer' => $mt,
        ]);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function complete(Request $request) {
        $data = $request->validate([
            'mt-transfer-id' => 'required|numeric',
            'mt-attachment'  => 'required|file',
        ]);

        $mt = MoneyTransfer::find($data['mt-transfer-id']);
        /** @var \Illuminate\Http\UploadedFile $file */
        $file = $data['mt-attachment'];
        $path = $file->store('/storage/transfers/'.$mt->id);

        if (! $path) {
            return redirect(url()->previous())->with([
                'error' => 'Hiba történt a fájl feltöltésekor',
            ]);
        }

        $mt->attachment_path = $path;
        $mt->completed_at    = Carbon::now();
        $mt->save();

        return redirect(action('MoneyTransferController@show', $mt))->with([
            'success' => 'Átutalás sikeresen teljesítve',
        ]);
    }

    /**
     * @param $transferId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($transferId) {
        $mt = $this->transferService->getTransfersQueryByUser(Auth::id())->find($transferId);

        if ($mt->attachment_path) {
            Storage::delete($mt->attachment_path);
        }

        try {
            // Kitöröljük egyesével a hozzá tartozó adatokat
            foreach ($mt->transferOrders as $mto) {
                $mto->delete();
            }

            // Kitöröljük magát az átutalást is
            $mt->delete();
        } catch (Exception $e) {
            Log::error('Hiba történt az átutalás törlésekor.');

            return redirect(url()->previous(action('MoneyTransferController@show', $mt)))->with([
                'error' => 'Hiba történt az átutalás törlésekor',
            ]);
        }

        return redirect(action('MoneyTransferController@index'))->with([
            'success' => 'Átutalás sikeresen törölve',
        ]);
    }

    /**
     * @param $transferId
     * @return false|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadAttachment($transferId) {
        if ($this->transferService->getTransfersQueryByUser(Auth::id())->find($transferId)) {
            return Storage::download(MoneyTransfer::find($transferId)->attachment_path);
        }

        Log::error('A felhasználó rossz azonosítóval próbált letölteni átutalási csatolmányt');

        return false;
    }
}
