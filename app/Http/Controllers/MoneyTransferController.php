<?php

namespace App\Http\Controllers;

use App\MoneyTransfer;
use App\Subesz\OrderService;
use App\Subesz\TransferService;
use App\Subesz\UserService;
use Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $transfers = $transfers->orderByDesc('created_at')->orderBy('completed_at')->orderByDesc('id')->paginate(25);

        return view('hq.transfers.index')->with([
            'transfers' => $transfers,
            'resellers' => $resellers,
            'filter'    => $filter,
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create() {
        return view('hq.transfers.create');
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request) {
        $data = $request->validate([
            'mt-csv-export' => 'required|file',
        ]);

        /** @var \Illuminate\Http\UploadedFile $file */
        $csvData    = $this->transferService->getDataFromCsv($data['mt-csv-export']);
        $errorCount = 0;
        foreach ($csvData as $reseller_id => $transfers) {
            $sum           = 0;
            $orderIds      = [];
            $reducedValues = [];
            foreach ($transfers as $transfer) {
                $reducedValue                               = round(floatval($transfer['Jutalékkal csökkentett összeg']));
                $sum                                        += $reducedValue;
                $orderIds[]                                 = $transfer['localOrder']->id;
                $reducedValues[$transfer['localOrder']->id] = $reducedValue;
            }

            // Elmentjük szervíz segítségével az átutalást
            $response   = $this->transferService->storeTransfer(intval($reseller_id), $orderIds, $reducedValues, $sum);
            $errorCount += $response['errors'];
        }

        // Végeztünk, visszadobjuk az Átutalások oldalra
        $message = 'Átutalások sikeresen elmentve.';
        if ($errorCount > 0) {
            $message .= sprintf(' (%d megrendelés nem lett rögzítve, mert már létezik hozzá átutalás.)', $errorCount);
        }

        return redirect(action('MoneyTransferController@index'))->with([
            'success' => $message,
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

    public function generateExcel(Request $request) {
        $data = $request->validate([
            'dl-transfer-ids' => 'required|json',
        ]);

        $transferIds = json_decode($data['dl-transfer-ids']);
        $transfers   = MoneyTransfer::whereIn('id', $transferIds)->get()->groupBy('user_id');

        // Létrehozzuk a táblázatot
        $spreadsheet = new Spreadsheet();
        $ws          = $spreadsheet->getActiveSheet();
        $row         = 2;
        $lastName    = '';

        $ws->setCellValue('A1', 'Viszonteladó');
        $ws->setCellValue('B1', 'Létrehozva');
        $ws->setCellValue('C1', 'Vásárló');
        $ws->setCellValue('D1', 'Összeg');

        foreach ($transfers as $resellerTransfers) {
            /** @var MoneyTransfer $moneyTransfer */
            //->getCellByColumnAndRow(2, 5)->getValue();
            foreach ($resellerTransfers as $moneyTransfer) {
                if ($lastName != $moneyTransfer->reseller->name) {
                    $ws->setCellValueByColumnAndRow(1, $row, $moneyTransfer->reseller->name);
                    $ws->setCellValueByColumnAndRow(2, $row, $moneyTransfer->created_at->format('Y.m.d'));

                    $lastName = $moneyTransfer->reseller->name;
                }

                /** @var \App\MoneyTransferOrder $to */
                foreach ($moneyTransfer->transferOrders as $to) {
                    $ws->setCellValueByColumnAndRow(3, $row, $to->order->firstname.' '.$to->order->lastname);
                    $ws->setCellValueByColumnAndRow(4, $row, number_format($to->order->total_gross, 0, '.', ' ').' Ft');
                    $row++;
                }
            }
        }

        // Formázás
        for ($i = 1; $i <= 4; $i++) {
            $ws->getColumnDimensionByColumn($i)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        try {
            $response = new StreamedResponse(function () use ($writer) {
                $writer->save('php://output');
            });
            $response->headers->set('Content-Type', 'application/vnd.ms-excel');
            $response->headers->set('Content-Disposition', 'attachment;filename="atutalasok_'.date('Ymd_his').'.xlsx"');
            $response->headers->set('Cache-Control', 'max-age=0');

            return $response;
        } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
            Log::error('Nem sikerült létrehozni a táblázatot.');
            Log::error($e->getMessage());
        }
    }
}
