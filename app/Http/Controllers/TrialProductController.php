<?php

namespace App\Http\Controllers;

use App\Product;
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
        $this->shoprenterService->updateProducts();

        return view('product.all')->with([
            'products' => Product::all()
        ]);
    }

    /**
     * @param $sku
     * @return Product[]|\Illuminate\Database\Eloquent\Collection
     * @throws \Exception
     */
    public function toggleProduct($sku) {
        $found = Product::find($sku);

        $found->trial_product = !$found->trial_product;
        $found->save();

        return Product::all();
    }
}
