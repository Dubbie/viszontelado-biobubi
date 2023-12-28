<?php

namespace App\Http\Controllers;

use App\Subesz\TransferService;
use Illuminate\Http\Request;

class SimpleController extends Controller
{
	protected TransferService $transferService;

	public function __construct(TransferService $transferService)
	{
		$this->transferService = $transferService;
	}

	/**
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function create()
	{
		return view('hq.transfers.simple.create');
	}

	/**
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function store(Request $request)
	{
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
}
