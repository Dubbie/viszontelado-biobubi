<?php

namespace App\Http\Controllers;

use App\BarionTransaction;
use App\Order;
use App\Subesz\TransferService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class BarionController extends Controller
{
	protected TransferService $transferService;

	public function __construct(TransferService $transferService)
	{
		$this->transferService = $transferService;
	}

	public function create()
	{
		return view('hq.transfers.barion.create');
	}

	public function store(Request $request)
	{
		$data = $request->validate([
			'mt-table-export' => 'required|file',
		]);

		$file = $data['mt-table-export'];
		$spreadsheet = $this->readExcelFile($file);

		$transactions = $this->getTransactionsFromSpreadsheet($spreadsheet);
		$transactionsByReseller = $this->groupTransactionsByReseller($transactions);
		$errorCount = 0;

		foreach ($transactionsByReseller as $resellerId => $transactionData) {
			$response = $this->transferService->storeTransfer($resellerId, $transactionData['orderIds'], $transactionData['reducedValues'], $transactionData['sum']);
			$errorCount += $response['errors'];
		}

		$message = 'Átutalások sikeresen elmentve.';
		if ($errorCount > 0) {
			$message .= sprintf(' (%d megrendelés nem lett rögzítve, mert már létezik hozzá átutalás.)', $errorCount);
		}

		return redirect(action('MoneyTransferController@index'))->with([
			'success' => $message,
		]);
	}

	private function readExcelFile(UploadedFile $file)
	{
		$reader = IOFactory::createReaderForFile($file->getRealPath());
		$reader->setReadDataOnly(true);

		return $reader->load($file->getRealPath());
	}

	private function getTransactionsFromSpreadsheet(Spreadsheet $spreadsheet): array
	{
		$worksheet = $spreadsheet->getActiveSheet();
		$transactions = [];

		foreach ($worksheet->getRowIterator() as $row) {
			// A: Tranzakció időpontja
			// B: Tranzakció típusa
			// C: Vásárló / Kedvezményezett
			// D: Tranzakció összege
			// E: Tranzakció utáni egyenleg
			// F: Deviza
			// G: Fizetés elfogadóhely általi azonosítója
			// H: Tranzakció elfogadóhely általái azonosítója
			// I: Fizetés Barion azonosító
			// J: Tranzakció Barion azonosító
			// K: Megrendelés elfogadóhely általi azonosítója
			// L: Kártya szám
			// M: Banki engedélykód
			// N: Megjegyzés
			$rowIndex = $row->getRowIndex();
			$transactionAcceptanceId = $worksheet->getCell('H' . $rowIndex)->getValue();
			$transactionAmount = $worksheet->getCell('D' . $rowIndex)->getValue();
			$transactionType = $worksheet->getCell('B' . $rowIndex)->getValue();
			$bubiPurchase = $transactionAcceptanceId === 'ShopRenterAffiliate';
			$refund = $transactionType === 'Visszatérítés kártyára';
			if ($rowIndex === 1 || !$bubiPurchase || $refund) {
				continue;
			}

			$barionTransaction = new BarionTransaction();
			$transactionDate = Carbon::createFromFormat('Y.m.d. H:i:s', $worksheet->getCell('A' . $rowIndex)->getValue());
			$barionTransaction->transactionDate = $transactionDate;
			$barionTransaction->transactionType = $transactionType;
			$barionTransaction->customer = $worksheet->getCell('C' . $rowIndex)->getValue();
			$barionTransaction->transactionAmount = $transactionAmount;
			$barionTransaction->transactionBalance = $worksheet->getCell('E' . $rowIndex)->getValue();
			$barionTransaction->transactionCurrencyIso = $worksheet->getCell('F' . $rowIndex)->getValue();
			$barionTransaction->paymentAcceptanceId = $worksheet->getCell('G' . $rowIndex)->getValue();
			$barionTransaction->transactionAcceptanceId = $transactionAcceptanceId;
			$barionTransaction->paymentBarionId = $worksheet->getCell('I' . $rowIndex)->getValue();
			$barionTransaction->transactionBarionId = $worksheet->getCell('J' . $rowIndex)->getValue();
			$barionTransaction->orderAcceptanceId = $worksheet->getCell('K' . $rowIndex)->getValue();
			$barionTransaction->comment = $worksheet->getCell('N' . $rowIndex)->getValue();

			$transactions[] = $barionTransaction;
		}

		return $transactions;
	}

	private function groupTransactionsByReseller(array $transactions): array
	{
		$transactionsByReseller = [];
		$transactions = $this->reduceTransactionsByFee($transactions);

		/** @var BarionTransaction $transaction */
		foreach ($transactions as $transaction) {
			$localOrder = Order::where('inner_id', $transaction->orderAcceptanceId)->first();
			$gatewayPayment = $transaction->transactionType === 'Gateway használati díj';
			if (!$localOrder) {
				Log::warning("Nem található megrendelés a megadott azonosítóval. (ID: {$transaction->orderAcceptanceId})");
				continue;
			}

			if ($gatewayPayment) {
				Log::debug("Gateway tranzakció, ezt kihagyjuk");
				continue;
			}

			// Check if reseller is in the array, if not, add it
			if (!isset($transactionsByReseller[$localOrder->reseller_id])) {
				$transactionsByReseller[$localOrder->reseller_id] = [
					'orderIds' => [],
					'reducedValues' => [],
					'sum' => 0,
				];
			}

			// Check if order is in the array, if not, add it
			if (!in_array($localOrder->id, $transactionsByReseller[$localOrder->reseller_id]['orderIds'])) {
				$transactionsByReseller[$localOrder->reseller_id]['orderIds'][] = $localOrder->id;
				$transactionsByReseller[$localOrder->reseller_id]['reducedValues'][$localOrder->id] = $transaction->transactionAmount;
				$transactionsByReseller[$localOrder->reseller_id]['sum'] += $transaction->transactionAmount;
			}
		}

		return $transactionsByReseller;
	}

	private function reduceTransactionsByFee(array &$transactions): array
	{
		/** @var BarionTransaction $transaction */
		foreach ($transactions as $transaction) {
			// Check if transaction type is Gateway használati díj
			if ($transaction->transactionType === 'Gateway használati díj') {
				$orderId = $transaction->orderAcceptanceId;

				foreach ($transactions as $innerTransaction) {
					if ($innerTransaction->orderAcceptanceId === $orderId && $innerTransaction->transactionAmount > 0) {
						Log::info("Megtaláltuk a megrendelést a használati díj alapján.");
						Log::info("- Tranzakció értéke előtte: {$innerTransaction->transactionAmount}");
						$innerTransaction->transactionAmount += $transaction->transactionAmount;
						Log::info("- Tranzakció értéke utána: {$innerTransaction->transactionAmount}");
					}
				}
			}
		}

		return $transactions;
	}
}
