<?php

namespace App\Http\Controllers;

use App\Stock;
use App\Subesz\ShoprenterService;
use App\Subesz\StockService;
use App\User;
use Auth;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Log;

class StockController extends Controller
{
    /** @var StockService */
    private StockService $stockService;

    /**
     * StockController constructor.
     *
     * @param  StockService  $stockService
     */
    public function __construct(StockService $stockService) {
        $this->stockService = $stockService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(): View|Factory|Application {
        return view('stock.index')->with([
            'stock' => Auth::user()->stock,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create(Request $request): Application|Factory|View {
        /** @var ShoprenterService $ss */
        $ss    = resolve('App\Subesz\ShoprenterService2');
        $items = $ss->getBasicProducts();

        return view('stock.create')->with([
            'items' => $items,
            'users' => User::all(),
            'hash'  => $request->server->get('REQUEST_TIME'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request): Application|RedirectResponse|Redirector {
        $data = $request->validate([
            'stock-user-id'    => 'required',
            'stock-item-sku'   => 'required|array',
            'stock-item-count' => 'required|array',
        ]);

        // Összerakjuk
        $stockData = $this->stockService->getProductDataFromInput($data['stock-item-sku'], $data['stock-item-count']);
        foreach ($stockData as $item) {
            // Elmentjük
            $this->stockService->addToStock(User::find($data['stock-user-id']), $item['sku'], $item['count']);
        }

        return redirect(action('StockController@adminIndex'))->with([
            'success' => 'Készlet sikeresen mentve',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $userId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($userId): Factory|\Illuminate\View\View {
        $user = User::find($userId);

        /** @var ShoprenterService $ss */
        $ss    = resolve('App\Subesz\ShoprenterService');
        $items = $ss->getBasicProducts();

        return view('stock.edit')->with([
            'items' => $items,
            'user'  => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param                            $userId
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $userId) {
        $data = $request->validate([
            'stock-item-sku'   => 'required|array',
            'stock-item-count' => 'required|array',
        ]);

        $user      = User::find($userId);
        $oldStock  = $user->stock;
        $newSkus   = [];
        $stockData = $this->stockService->getProductDataFromInput($data['stock-item-sku'], $data['stock-item-count']);
        foreach ($stockData as $item) {
            // Megnézzük, van-e elmentve készlet belőle
            /** @var Stock $stock */
            $stock = $user->stock()->where('sku', $item['sku'])->first();
            if ($stock && $item['count'] != $stock->inventory_on_hand) {
                // 1. eset: Szerepl már az adatbázisban az SKU
                //          - Megnézzük, mennyivel tér el
                $this->stockService->updateStock($user, $stock->id, $item['count']);
            } else {
                if (! $stock) {
                    // 2. eset: Nem szerepel még az adatbázisban az SKU
                    //          - Hozzáadjuk
                    $this->stockService->addToStock($user, $item['sku'], $item['count']);
                } else {
                    // Nem történik semmit, ugyanaz volt ami lett
                    Log::info(sprintf('A készlet megegyezik a régivel (%s db %s)', $stock->inventory_on_hand, $stock->product->name));
                }
            }

            // Hozzáadjuk az SKU-t, hogy össze tudjuk hasonlítani, mi van az adatbázisban.
            $newSkus[] = $item['sku'];
        }

        foreach ($oldStock as $oldSku) {
            if (! in_array($oldSku->sku, $newSkus)) {
                try {
                    $oldSku->delete();
                } catch (Exception $e) {
                    Log::error('Hiba történt az adatbázisban tárold készlet törlésekor!');
                    Log::error(sprintf('%s %s', $e->getCode(), $e->getMessage()));
                }
            }
        }

        return redirect(action('CentralStockController@index'))->with([
            'success' => 'Készlet sikeresen frissítve',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Stock  $stock
     * @return \Illuminate\Http\Response
     */
    public function destroy(Stock $stock) {
        //
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function createRow(Request $request) {
        /** @var ShoprenterService $ss */
        $ss    = resolve('App\Subesz\ShoprenterService');
        $items = $ss->getBasicProducts();

        return view('inc.stock-row')->with([
            'items' => $items,
            'users' => User::all(),
            'hash'  => $request->server->get('REQUEST_TIME'),
        ]);
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function fetch($userId): mixed {
        $user = User::find($userId);

        return $user->stock->load('product', 'reseller');
    }

    public function getResellerStockBySKU($userId, $sku) {
        $stockEntry = Stock::where([
            ['user_id', '=', $userId],
            ['sku', '=', $sku],
        ])->first();

        if (! $stockEntry) {
            return 0;
        }

        return $stockEntry->inventory_on_hand;
    }
}
