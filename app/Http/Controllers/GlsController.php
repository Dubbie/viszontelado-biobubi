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

    public function __construct(GlsService $glsService) {
        $this->glsService = $glsService;
    }

    public function test() {

      dd($this->glsService->getParcelList(Carbon::now()->startOfDay(), Carbon::now()));
    }
}
