<?php

namespace App\Http\Controllers;

use App\BundleProduct;
use App\Product;
use App\Subesz\ShoprenterService;
use App\Subesz\StockService;
use Illuminate\Http\Request;

class BundleController extends Controller
{
    /** @var StockService */
    private StockService $stockService;

    /**
     * BundleController constructor.
     *
     * @param  StockService  $stockService
     */
    public function __construct(StockService $stockService) {
        $this->stockService = $stockService;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index() {
        return view('product.bundle.index')->with([
            'products' => $this->stockService->getBundles(),
        ]);
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Request $request) {
        /** @var ShoprenterService $ss */
        $ss = resolve('App\Subesz\ShoprenterService');
        $ss->updateProducts();

        return view('product.bundle.create')->with([
            'products' => $this->stockService->getBaseProducts(),
            'hash'     => $request->server->get('REQUEST_TIME'),
        ]);
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function row(Request $request) {
        return view('inc.bundle-product-row')->with([
            'products' => $this->stockService->getBaseProducts(),
            'hash'     => $request->server->get('REQUEST_TIME'),
        ]);
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request) {
        $data = $request->validate([
            'bundle-sku'           => 'required',
            'bundle-product-sku'   => 'required|array',
            'bundle-product-count' => 'required|array',
        ]);

        if (in_array($data['bundle-sku'], $data['bundle-product-sku'])) {
            return redirect(url()->previous())->with([
                'error' => 'A rész termékek közt nem szerepelhet a csomag. GttG',
            ]);
        }

        $productsList = $this->stockService->getProductDataFromInput($data['bundle-product-sku'], $data['bundle-product-count']);
        foreach ($productsList as $subProduct) {
            $bundleProduct              = new BundleProduct();
            $bundleProduct->bundle_sku  = $data['bundle-sku'];
            $bundleProduct->product_sku = $subProduct['sku'];
            $bundleProduct->product_qty = $subProduct['count'];
            $bundleProduct->save();
        }

        return redirect(action('BundleController@index'))->with([
            'success' => 'Csomag sikeresen létrehozva',
        ]);
    }

    /**
     * @param $bundleSku
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($bundleSku) {
        /** @var ShoprenterService $ss */
        $ss = resolve('App\Subesz\ShoprenterService');
        $ss->updateProducts();
        $bundle = Product::find($bundleSku);

        return view('product.bundle.edit')->with([
            'bundle'   => $bundle,
            'products' => $this->stockService->getBaseProducts(),
        ]);
    }

    /**
     * @param  Request  $request
     * @param           $bundleSku
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $bundleSku) {
        $data = $request->validate([
            'bundle-product-sku'   => 'required|array',
            'bundle-product-count' => 'required|array',
        ]);

        $product     = Product::find($bundleSku);
        $oldBundle   = $product->subProducts;
        $newSkus     = [];
        $productData = $this->stockService->getProductDataFromInput($data['bundle-product-sku'], $data['bundle-product-count']);
        foreach ($productData as $item) {
            // Megnézzük, van-e elmentve készlet belőle /** @var Product $pro */
            /** @var BundleProduct $subProduct */
            $subProduct = $product->subProducts()->where('product_sku', $item['sku'])->first();
            if ($subProduct && $item['count'] != $subProduct->product_qty) {
                // 1. eset: Szerepl már az adatbázisban az SKU
                //          - Megnézzük, mennyivel tér el
                $subProduct->product_qty = $item['count'];
                $subProduct->save();

                \Log::info(sprintf('A "%s" cikkszámú csomaghoz tartozó "%s" cikkszámú (%s) rész termék új mennyisége: %s db', $product->sku, $subProduct->product_sku, $subProduct->product->name, $subProduct->product_qty));
            } else {
                if (! $subProduct) {
                    // 2. eset: Nem szerepel még az adatbázisban az SKU
                    //          - Hozzáadjuk
                    $subProduct              = new BundleProduct();
                    $subProduct->bundle_sku  = $product->sku;
                    $subProduct->product_sku = $item['sku'];
                    $subProduct->product_qty = $item['count'];
                    $subProduct->save();

                    \Log::info(sprintf('A "%s" cikkszámú csomaghoz ÚJ rész termék lett rögzítve: %s db %s (Cikkszám: "%s")', $product->sku, $subProduct->product_qty, $subProduct->product->name, $subProduct->product_sku));
                } else {
                    // Nem történik semmit, ugyanaz volt ami lett
                    \Log::info(sprintf('A csomag részterméke megegyezik a régivel (%s db %s)', $subProduct->product_qty, $subProduct->product->name));
                }
            }

            // Hozzáadjuk az SKU-t, hogy össze tudjuk hasonlítani, mi van az adatbázisban.
            $newSkus[] = $item['sku'];
        }

        /** @var BundleProduct $oldBundleProduct */
        foreach ($oldBundle as $oldBundleProduct) {
            if (! in_array($oldBundleProduct->product_sku, $newSkus)) {
                try {
                    $oldBundleProduct->delete();
                } catch (\Exception $e) {
                    \Log::error('Hiba történt az adatbázisban tárolt résztermék törlésekor!');
                    \Log::error(sprintf('%s %s', $e->getCode(), $e->getMessage()));
                }
            }
        }

        return redirect(action('BundleController@index'))->with([
            'success' => 'Csomag sikeresen frissítve',
        ]);
    }

    /**
     * @param $bundleSku
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($bundleSku) {
        BundleProduct::where('bundle_sku', $bundleSku)->delete();

        return redirect(action('BundleController@index'))->with([
            'success' => 'Csomag sikeresen törölve',
        ]);
    }
}
