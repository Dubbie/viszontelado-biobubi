<?php

namespace App\Http\Controllers;

use App\Subesz\ShoprenterService;
use App\TrialProduct;

class TrialProductController extends Controller
{
    /** @var ShoprenterService  */
    private $shoprenterService;

    /**
     * TrialProductController constructor.
     * @param ShoprenterService $shoprenterService
     */
    public function __construct(ShoprenterService $shoprenterService)
    {
        $this->shoprenterService = $shoprenterService;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function listProducts() {
        $productsResponse = $this->shoprenterService->getAllProducts();
        $trials = TrialProduct::all('sku')->pluck('sku')->toArray();

        return view('products')->with([
            'products' => $productsResponse->items,
            'trials' => $trials,
        ]);
    }

    /**
     * @param $sku
     * @return TrialProduct[]|\Illuminate\Database\Eloquent\Collection
     * @throws \Exception
     */
    public function toggleProduct($sku) {
        $found = TrialProduct::where('sku', $sku)->first();

        // Ha benne van akkor töröljük, ha nincs benne akkor hozzáadjuk
        if ($found) {
            $found->delete();
        } else {
            $trial = new TrialProduct();
            $trial->sku = $sku;
            $trial->save();
        }

        return TrialProduct::all();
    }
}
