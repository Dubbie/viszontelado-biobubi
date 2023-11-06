<?php

namespace App\Http\Controllers;

use App\Subesz\TharanisService;

class TharanisController extends Controller
{
	private TharanisService $tharanisService;

	public function __construct(TharanisService $tharanisService)
	{
		$this->tharanisService = $tharanisService;
	}

	public function test()
	{
		$testID = "";
		$invoiceID = "";

		$ss = resolve('App\Subesz\ShoprenterService');
		$srOrder = $ss->getOrder($testID);

		dd($this->tharanisService->createInvoice($srOrder));
	}
}
