<?php

namespace App\Http\Controllers;

use App\Stock;
use App\Subesz\ShoprenterService;
use App\Subesz\StockService;
use App\User;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /** @var StockService  */
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
        foreach ($data['stock-item-sku'] as $key => $item) {
            $split = explode('|', $item);

            // Elmentjük
            $this->stockService->addToStock(
                User::find($data['stock-user-id']),
                \Auth::user(),
                $split[0],
                $split[1],
                str_replace(' ', '', $data['stock-item-count'][$key])
            );
        }

        return redirect(action('StockController@index'))->with([
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
     * @param  \App\Stock $stock
     * @return \Illuminate\Http\Response
     */
    public function edit(Stock $stock)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Stock $stock
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Stock $stock)
    {
        //
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

    public function adminIndex() {
        return view('stock.admin-index')->with([
            'users' => User::all(),
        ]);
    }
}
