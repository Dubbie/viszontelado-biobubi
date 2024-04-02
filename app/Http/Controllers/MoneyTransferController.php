<?php

namespace App\Http\Controllers;

use App\MoneyTransfer;
use App\Subesz\BillingoNewService;
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
    private UserService $userService;

    /** @var \App\Subesz\OrderService */
    private OrderService $orderService;

    /** @var \App\Subesz\TransferService */
    private TransferService $transferService;

    private BillingoNewService $billingoNewService;

    private string $sessionResellerKey;

    private string $sessionOrderIdsKey;

    /**
     * MoneyTransferController constructor.
     *
     * @param  \App\Subesz\OrderService        $orderService
     * @param  \App\Subesz\TransferService     $transferService
     * @param  \App\Subesz\UserService         $userService
     * @param  \App\Subesz\BillingoNewService  $billingoNewService
     */
    public function __construct(
        OrderService $orderService,
        TransferService $transferService,
        UserService $userService,
        BillingoNewService $billingoNewService,
    ) {
        $this->orderService       = $orderService;
        $this->transferService    = $transferService;
        $this->userService        = $userService;
        $this->billingoNewService = $billingoNewService;
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
     * @param  \Illuminate\Http\Request  $request
     */
    public function multiGenerateCommissions(Request $request) {
        $data = $request->validate([
            'com-transfer-ids' => 'required|json',
        ]);

        $invoices    = [];
        $mts         = $this->transferService->getTransfersQueryByUser(Auth::id())->whereIn('id', json_decode($data['com-transfer-ids']))->get();
        $err         = false;
        $errMessages = collect();
        $success     = 0;

        foreach ($mts as $mt) {
            if ($mt->invoice_id) {
                return redirect(url()->previous(action('MoneyTransferController@index')))->with([
                    'error' => 'Az alábbi átutaláshoz már készült számla, kérlek ne jelöld be: '.$mt->getId(),
                ]);
            }

            if (! array_key_exists($mt->user_id, $invoices)) {
                $invoices[$mt->user_id] = [
                    'commission'   => 0,
                    'ids'          => [],
                    'user_data'    => $mt->reseller,
                    'billing_data' => $mt->reseller->details,
                    'transfers'    => [],
                ];
            }

            $invoices[$mt->user_id]['commission']  += $mt->getCommissionFee();
            $invoices[$mt->user_id]['ids'][]       = $mt->getId();
            $invoices[$mt->user_id]['transfers'][] = $mt;
        }

        try {
            foreach ($invoices as $inv) {
                $fee         = $inv['commission'];
                $ids         = $inv['ids'];
                $userData    = $inv['user_data'];
                $billingData = $inv['billing_data'];
                // Megnézzük, hogy van-e mindenünk a számla gyártásához.
                Log::info(sprintf('Jutalék számla készítése (Azonosító: %s)', implode(',', $ids)));
                if (! $billingData) {
                    Log::error('A viszonteladónak nincs elmentve számlázási adat: '.$userData->name);
                    $errMessages->add('A viszonteladónak nincs elmentve számlázási adat: '.$userData->name);
                    $err = true;
                } else {
                    if (! $billingData['billing_address_id'] || ! $billingData['billing_name'] || ! $billingData['billing_tax_number'] || ! $billingData['billing_account_number']) {
                        Log::error('A viszonteladónak hiányzik egy vagy több számlázási adata: '.$userData->name);
                        $errMessages->add('A viszonteladónak hiányzik egy vagy több számlázási adata: '.$userData->name);
                        $err = true;
                    }
                }

                if (! $err) {
                    // Csináljuk a számlát
                    $partner = $this->billingoNewService->createResellerPartner($billingData);

                    if (! $partner) {
                        $errMessages->add('Hiba történt a viszonteladó létrehozásakor a Billingo API-n keresztül.');
                        Log::error('Hiba történt a viszonteladó létrehozásakor a Billingo API-n keresztül.');
                    }

                    $invoice = $this->billingoNewService->createCommissionInvoice($partner, $fee, $ids);
                    if (! $invoice) {
                        $errMessages->add('Hiba történt a számla létrehozásakor a Billingo API-n keresztül.');
                        Log::error('Hiba történt a számla létrehozásakor a Billingo API-n keresztül.');
                    } else {
                        foreach ($inv['transfers'] as $mt) {
                            $mt->invoice_id = $invoice->getId();
                            $mt->save();
                        }
                        $success++;
                        Log::info('Jutalék számla sikeresen létrehozva. (ID: '.$invoice->getId().')');
                    }
                }
            }
        } catch (Exception $e) {
            Log::error('Hiba történt a számlák generálásakor.');
            Log::error($e->getMessage());

            return redirect(url()->previous(action('MoneyTransferController@index')))->with([
                'error' => 'Hiba történt a számlák generálásakor. '.$e->getMessage(),
            ]);
        }

        if (! $err) {
            return redirect(action('MoneyTransferController@index'))->with([
                'success' => sprintf('Jutalék számlák sikeresen legenerálva (%d db)', $success),
            ]);
        } else {
            $errMessages->prepend(sprintf('Jutalék számlák kiállítva: %d db', $success));

            return redirect(url()->previous(action('MoneyTransferController@index')))->with([
                'errors' => $errMessages,
            ]);
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function multiDestroy(Request $request) {
        $data = $request->validate([
            'destroy-transfer-ids' => 'required|json',
        ]);
        $mts  = $this->transferService->getTransfersQueryByUser(Auth::id())->whereIn('id', json_decode($data['destroy-transfer-ids']))->get();

        foreach ($mts as $mt) {
            if ($mt->attachment_path) {
                Storage::delete($mt->attachment_path);
            }
        }

        try {
            // Kitöröljük egyesével a hozzá tartozó adatokat
            foreach ($mts as $mt) {
                foreach ($mt->transferOrders as $mto) {
                    $mto->delete();
                }

                // Kitöröljük magát az átutalást is
                $mt->delete();
            }
        } catch (Exception $e) {
            Log::error('Hiba történt az átutalás törlésekor.');

            return redirect(url()->previous(action('MoneyTransferController@index')))->with([
                'error' => 'Hiba történt az átutalások törlésekor',
            ]);
        }

        return redirect(action('MoneyTransferController@index'))->with([
            'success' => 'Átutalások sikeresen törölve',
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
        $transfers   = MoneyTransfer::whereIn('id', $transferIds)->where('user_id', Auth::id())->get()->groupBy('user_id');
        if (Auth::user()->admin) {
            $transfers   = MoneyTransfer::whereIn('id', $transferIds)->get()->groupBy('user_id');
        }

        /** @var \App\Subesz\BillingoNewService $bs */
        $bs = resolve('App\Subesz\BillingoNewService');

        // Létrehozzuk a táblázatot
        $spreadsheet = new Spreadsheet();
        $ws          = $spreadsheet->getActiveSheet();
        $row         = 2;
        $lastName    = '';

        $ws->setCellValue('A1', 'Viszonteladó');
        $ws->setCellValue('B1', 'Létrehozva');
        $ws->setCellValue('C1', 'Végszámla');
        $ws->setCellValue('D1', 'Előlegszámla');
        $ws->setCellValue('E1', 'Fizetett Összeg');
        $ws->setCellValue('F1', 'Jutalékkal csökkentett Összeg');
        $ws->setCellValue('G1', 'Vásárló');
		$ws->setCellValue('H1', 'Rendelés Azonosító');
		$ws->setCellValue('I1', 'E-mail cím');

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
                    // Kiszedjük a számláját
                    $localOrder = $to->order;
                    if ($localOrder->invoice_id) {
                        $invoice = $bs->getInvoice($localOrder->invoice_id, $moneyTransfer->reseller);
                        if ($invoice) {
                            $ws->setCellValueByColumnAndRow(3, $row, $invoice->getInvoiceNumber());
                        }
                    }
                    if ($localOrder->advance_invoice_id) {
                        $advanceInvoice = $bs->getInvoice($localOrder->advance_invoice_id, $moneyTransfer->reseller);
                        if ($advanceInvoice) {
                            $ws->setCellValueByColumnAndRow(4, $row, $advanceInvoice->getInvoiceNumber());
                        }
                    }
                    //$ws->setCellValueByColumnAndRow(5, $row, number_format($to->order->total_gross, 0, '.', ' ').' Ft');
                    $ws->setCellValueByColumnAndRow(5, $row, $to->order->total_gross);
                    $ws->getCellByColumnAndRow(5, $row)->getStyle()->getNumberFormat()->setFormatCode('# ##0 Ft');
                    if ($to->reduced_value) {
                        $ws->setCellValueByColumnAndRow(6, $row, $to->reduced_value);
                        $ws->getCellByColumnAndRow(6, $row)->getStyle()->getNumberFormat()->setFormatCode('# ##0 Ft');
                    } else {
                        $ws->setCellValueByColumnAndRow(6, $row, '-');
                    }
                    $ws->setCellValueByColumnAndRow(7, $row, $to->order->firstname.' '.$to->order->lastname);
					$ws->setCellValueByColumnAndRow(8, $row, $to->order->inner_id);
					$ws->setCellValueByColumnAndRow(9, $row, $to->order->email);
                    $row++;
                }
            }
        }

        // Formázás
        for ($i = 1; $i <= 10; $i++) {
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