<?php

namespace App\Http\Controllers;

use App\Subesz\GlsService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;

class GlsController extends Controller
{
	private GlsService $glsService;

	/**
	 * Constructor for the class.
	 *
	 * @param GlsService $glsService The GlsService dependency.
	 */
	public function __construct(GlsService $glsService)
	{
		$this->glsService = $glsService;
	}

	public function test()
	{
		if ($this->glsService->isApiWorking()) {
			echo "GLS API is working";
		} else {
			echo "GLS API is not working";
		}
	}
}
