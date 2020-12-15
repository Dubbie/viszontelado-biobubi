<?php

namespace App\Http\Controllers;

use App\CentralStock;
use App\Product;
use App\Subesz\StockService;
use App\User;
use Illuminate\Http\Request;

class CentralStockController extends Controller
{
    /** @var StockService */
    private $stockService;

    /**
     * CentralStockController constructor.
     * @param StockService $stockService
     */
    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('hq.stock-index')->with([
            'users' => User::all(),
            'products' => resolve('App\Subesz\StockService')->getBaseProducts()
        ]);
    }

    /**
     * @param bool $first
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCentralStockRow($first = false)
    {
        return resolve('App\Subesz\StockService')->getCentralStockRow($first);
    }

    /**
     * @param bool $first
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getResellerStockRow($first = false)
    {
        return resolve('App\Subesz\StockService')->getResellerStockRow($first);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function stockHtml()
    {
        return resolve('App\Subesz\StockService')->getCentralStockHTML();
    }

    /**
     * @param $userId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function resellerStockHtml($userId)
    {
        return resolve('App\Subesz\StockService')->getResellerStockListHTML($userId);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'cs-new-product' => 'required|array',
            'cs-new-product-qty' => 'required|array',
        ]);

        // Rendezzük
        $uniques = [];
        foreach ($data['cs-new-product'] as $key => $sku) {
            if (!array_key_exists($sku, $uniques)) {
                $uniques[$sku] = intval($data['cs-new-product-qty'][$key]);
            } else {
                $uniques[$sku] += intval($data['cs-new-product-qty'][$key]);
            }
        }

        // Most mentjük el
        foreach ($uniques as $sku => $qty) {
            $this->stockService->addToCentralStock($sku, $qty);
        }

        // Visszatérünk
        if ($request->ajax()) {
            return response()->json([
                'code' => 200,
                'message' => 'Központi készlet sikeresen hozzáadva',
                'csListHTML' => $this->stockService->getCentralStockHTML(),
                'csNewHTML' => $this->stockService->getCentralStockRow(true),
            ], 200);
        } else {
            return redirect(action('CentralStockController@index'))->with([
                'success' => 'Központi készlet sikeresen hozzáadva',
            ]);
        }
    }

    public function addStockToReseller(Request $request)
    {
        $data = $request->validate([
            'rs-add-reseller-id' => 'required',
            'rs-add-stock' => 'required|array',
            'rs-add-stock-qty' => 'required|array',
        ]);

        // Rendezzük
        $uniques = [];
        foreach ($data['rs-add-stock'] as $key => $sku) {
            if (!array_key_exists($sku, $uniques)) {
                $uniques[$sku] = intval($data['rs-add-stock-qty'][$key]);
            } else {
                $uniques[$sku] += intval($data['rs-add-stock-qty'][$key]);
            }
        }

        // Most mentjük el
        $reseller = User::find($data['rs-add-reseller-id']);
        if (!$reseller) {
            return redirect(action('CentralStockController@index'))->with([
                'error' => 'Nem található ilyen viszonteladó',
            ]);
        }

        foreach ($uniques as $sku => $qty) {
            $this->stockService->addToStock($reseller, $sku, $qty);
        }

        return redirect(url()->previous())->with([
            'success' => 'Viszonteladó készlete sikeresen frissítve',
        ]);
    }
}