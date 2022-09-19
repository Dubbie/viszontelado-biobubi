<?php

namespace App\Http\Controllers;

use App\Product;
use App\StockMovement;
use App\Subesz\StockService;
use App\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class CentralStockController extends Controller
{
    /** @var \App\Subesz\StockService */
    private StockService $stockService;

    /**
     * CentralStockController constructor.
     *
     * @param  StockService  $stockService
     */
    public function __construct(StockService $stockService) {
        $this->stockService = $stockService;
    }

    /**
     * Fő központi készlet nézet, itt listázza ki az összes készleten lévő terméket a központban.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(): Factory|\Illuminate\View\View {
        return view('hq.stock.index')->with([
            'products' => $this->stockService->getBaseProducts(),
        ]);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function addStockToReseller(Request $request): Redirector|RedirectResponse|Application {
        $data = $request->validate([
            'os-reseller-id' => 'required',
            'os-sku'         => 'required',
            'os-amount'      => 'required',
        ]);

        $reseller = User::find($data['os-reseller-id']);
        if (! $reseller) {
            return redirect(action('CentralStockController@index'))->with([
                'error' => 'Nem található ilyen viszonteladó',
            ]);
        }

        $sku    = $data['os-sku'];
        $amount = str_replace(' ', '', $data['os-amount']);

        // Szervíz segítségével intézzük a mozgással járó feladatokat.
        $this->stockService->addToStock($reseller, $sku, $amount);

        return redirect(action('CentralStockController@index'))->with([
            'success' => 'Viszonteladó készlete sikeresen frissítve',
        ]);
    }

    /**
     * A paraméterben átadott cikkszám alapján betölti, hogy milyen terméket olvasott le.
     * Ezt utána hozzátudja adni magához, vagy a viszonteladóhoz.
     *
     * @param $sku
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function scanResult($sku): View|Factory|Application {
        // Megkeressük a kívánt terméket, ha nincs ilyen a helyi adatbázisban, akkor megnézzük, hogy az éles listában van-e ilyen.
        $product = Product::where('sku', $sku)->first();
        if (! $product) {
            // TODO:
            dd('Nincs ilyen termék');
        }

        // Volt termék, mutassuk a nézetet ami ide tartozik.
        $inventoryOnHand = number_format($this->stockService->getCentralStockOnHand($sku), 0, '.', ' ');

        return view('hq.stock.scan-result')->with([
            'product'         => $product,
            'inventoryOnHand' => $inventoryOnHand,
        ]);
    }

    /**
     * A paraméterben megadott cikkszám alapján mutatja a nézetet, ahol a központi készlethez kíván feltölteni.
     *
     * @param $sku
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function incoming($sku): View|Factory|Application {
        // Megkeressük a kívánt terméket, ha nincs ilyen a helyi adatbázisban, akkor megnézzük, hogy az éles listában van-e ilyen.
        $product = Product::where('sku', $sku)->first();
        if (! $product) {
            // TODO:
            dd('Nincs ilyen termék');
        }

        // Volt termék, mutassuk a nézetet ami ide tartozik.
        $inventoryOnHand = number_format($this->stockService->getCentralStockOnHand($sku), 0, '.', ' ');

        return view('hq.stock.incoming')->with([
            'product'         => $product,
            'inventoryOnHand' => $inventoryOnHand,
        ]);
    }

    /**
     * @param $sku
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function toReseller($sku): View|Factory|Application {
        // Megkeressük a kívánt terméket, ha nincs ilyen a helyi adatbázisban, akkor megnézzük, hogy az éles listában van-e ilyen.
        $product = Product::where('sku', $sku)->first();
        if (! $product) {
            // TODO:
            dd('Nincs ilyen termék');
        }

        // Volt termék, mutassuk a nézetet ami ide tartozik.
        $inventoryOnHand = number_format($this->stockService->getCentralStockOnHand($sku), 0, '.', ' ');

        return view('hq.stock.to-reseller')->with([
            'product'         => $product,
            'inventoryOnHand' => $inventoryOnHand,
            'users'           => User::whereHas('regions')->get(),
        ]);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
     */
    public function handleIncoming(Request $request): Redirector|RedirectResponse|Application {
        $data = $request->validate([
            'is-sku'    => 'required',
            'is-amount' => 'required',
        ]);

        // Mennyiség amennyivel növelni kell a meglévőt
        $amount = intval(str_replace(' ', '', $data['is-amount']));

        // Megkeressük a kívánt terméket, ha nincs ilyen a helyi adatbázisban, akkor megnézzük, hogy az éles listában van-e ilyen.
        $product = Product::where('sku', $data['is-sku'])->first();

        if (! $product) {
            // TODO:
            dd('Nincs ilyen termék');
        }

        // Adjuk hozzá a központhoz.
        $this->stockService->addToCentralStock($data['is-sku'], $amount);

        return redirect(action('CentralStockController@index'))->with([
            'success' => 'Készlet sikeresen elmentve!',
        ]);
    }

    /**
     * Központi készlethez tartozó történetet mutatja meg.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function history(): View|Factory|Application {
        return view('hq.stock.history')->with([
            'movements' => StockMovement::orderByDesc('created_at')->paginate(10),
            'products'  => $this->stockService->getBaseProducts(),
        ]);
    }
}