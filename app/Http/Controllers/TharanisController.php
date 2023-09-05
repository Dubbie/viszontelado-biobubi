<?php

namespace App\Http\Controllers;

use App\Subesz\TharanisService;

class TharanisController extends Controller
{
    private TharanisService $tharanisService;

    public function __construct(TharanisService $tharanisService) {
        $this->tharanisService = $tharanisService;
    }

    public function test() {
        $testID = "b3JkZXItb3JkZXJfaWQ9NjcxNjY=";

        $ss = resolve('App\Subesz\ShoprenterService');
        $srOrder = $ss->getOrder($testID);

        dd($this->tharanisService->createInvoice($srOrder));
        //dd($this->tharanisService->test());
        //dd($this->tharanisService->downloadInvoice('SA23/H002781'));
        //dd($this->tharanisService->getPaymentMethods());
        //dd($this->tharanisService->getShippingMethod());
    }
}
