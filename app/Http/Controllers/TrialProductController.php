<?php

namespace App\Http\Controllers;

use App\Product;
use App\Subesz\ShoprenterService;
use App\TrialProduct;
use Illuminate\Http\Request;

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
            'products' => Product::orderByDesc('status')->orderBy('name')->get()
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function editProduct(Request $request) {
        $data = $request->validate([
            'ep-product-sku' => 'required',
            'ep-purchase-price' => 'required',
            'ep-wholesale-price' => 'required',
        ]);

        $product = Product::find($data['ep-product-sku']);
        if (!$product) {
            \Log::error(sprintf('Hiba a termék frissítésekor, nincs ilyen cikkszámú termék. (%s)', $data['ep-product-sku']));
            return redirect(action('TrialProductController@listProducts'))->with([
                'error' => sprintf('Hiba a termék frissítésekor, nincs ilyen cikkszámú termék. (%s)', $data['ep-product-sku']),
            ]);
        }
        $product->purchase_price = $data['ep-purchase-price'];
        $product->wholesale_price = $data['ep-wholesale-price'];
        $product->save();

        return redirect(action('TrialProductController@listProducts'))->with([
            'success' => 'Termék sikeresen frissítve',
        ]);
    }
}
