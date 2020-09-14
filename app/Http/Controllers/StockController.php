<?php

namespace App\Http\Controllers;

use App\Stock;
use App\Subesz\ShoprenterService;
use App\Subesz\StockService;
use App\User;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /** @var StockService */
    private $stockService;

    /**
     * StockController constructor.
     * @param StockService $stockService
     */
    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('stock.index')->with([
            'stock' => \Auth::user()->stock,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        /** @var ShoprenterService $ss */
        $ss = resolve('App\Subesz\ShoprenterService');
        $items = $ss->getBasicProducts();

        return view('stock.create')->with([
            'items' => $items,
            'users' => User::all(),
            'hash' => $request->server->get('REQUEST_TIME'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'stock-user-id' => 'required',
            'stock-item-sku' => 'required|array',
            'stock-item-count' => 'required|array',
        ]);

        // Összerakjuk
        $stockData = $this->stockService->getProductDataFromInput($data['stock-item-sku'], $data['stock-item-count']);
        foreach ($stockData as $item) {
            // Elmentjük
            $this->stockService->addToStock(
                User::find($data['stock-user-id']),
                \Auth::user(),
                $item['sku'],
                $item['count']
            );
        }

        return redirect(action('StockController@adminIndex'))->with([
            'success' => 'Készlet sikeresen mentve'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Stock $stock
     * @return \Illuminate\Http\Response
     */
    public function show(Stock $stock)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $userId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($userId)
    {
        $user = User::find($userId);

        /** @var ShoprenterService $ss */
        $ss = resolve('App\Subesz\ShoprenterService');
        $items = $ss->getBasicProducts();

        return view('stock.edit')->with([
            'items' => $items,
            'user' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param $userId
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $userId)
    {
        $data = $request->validate([
            'stock-item-sku' => 'required|array',
            'stock-item-count' => 'required|array',
        ]);

        $user = User::find($userId);
        $oldStock = $user->stock;
        $newSkus = [];
        $stockData = $this->stockService->getProductDataFromInput($data['stock-item-sku'], $data['stock-item-count']);
        foreach ($stockData as $item) {
            // Megnézzük, van-e elmentve készlet belőle
            /** @var Stock $stock */
            $stock = $user->stock()->where('sku', $item['sku'])->first();
            if ($stock && $item['count'] != $stock->inventory_on_hand) {
                // 1. eset: Szerepl már az adatbázisban az SKU
                //          - Megnézzük, mennyivel tér el
                $this->stockService->updateStock($user, \Auth::user(), $stock->id, $item['count']);
            } else if (!$stock) {
                // 2. eset: Nem szerepel még az adatbázisban az SKU
                //          - Hozzáadjuk
                $this->stockService->addToStock($user, \Auth::user(), $item['sku'], $item['count']);
            } else {
                // Nem történik semmit, ugyanaz volt ami lett
                \Log::info(sprintf('A készlet megegyezik a régivel (%s db %s)', $stock->inventory_on_hand, $stock->name));
            }

            // Hozzáadjuk az SKU-t, hogy össze tudjuk hasonlítani, mi van az adatbázisban.
            $newSkus[] = $item['sku'];
        }

        foreach ($oldStock as $oldSku) {
            if (!in_array($oldSku->sku, $newSkus)) {
                try {
                    $oldSku->delete();
                } catch (\Exception $e) {
                    \Log::error('Hiba történt az adatbázisban tárold készlet törlésekor!');
                    \Log::error(sprintf('%s %s', $e->getCode(), $e->getMessage()));
                }
            }
        }

        return redirect(action('StockController@adminIndex'))->with([
            'success' => 'Készlet sikeresen frissítve',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Stock $stock
     * @return \Illuminate\Http\Response
     */
    public function destroy(Stock $stock)
    {
        //
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function createRow(Request $request)
    {
        /** @var ShoprenterService $ss */
        $ss = resolve('App\Subesz\ShoprenterService');
        $items = $ss->getBasicProducts();

        return view('inc.stock-row')->with([
            'items' => $items,
            'users' => User::all(),
            'hash' => $request->server->get('REQUEST_TIME'),
        ]);
    }

    public function adminIndex()
    {
        return view('stock.admin-index')->with([
            'users' => User::all(),
        ]);
    }
}
