<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(['register' => false]);

Route::group(['middleware' => 'auth'], function () {
    /**
     * Adminisztrátori jogot igénylő routeok.
     */
    Route::group(['middleware' => 'admin'], function () {
        Route::get('/felhasznalok', 'UserController@index');
        Route::get('/felhasznalok/uj/fiok', 'UserController@create');
        Route::post('/felhasznalok/mentes', 'UserController@store');
        Route::get('/felhasznalok/{userId}/megrendelesek', 'UserController@orders');
        Route::get('/felhasznalok/{userId}/szerkesztes', 'UserController@edit');
        Route::put('/felhasznalok/{userId}/frissites', 'UserController@update');
        Route::get('/felhasznalok/{userId}', 'UserController@show');

        Route::post('/megrendelesek/viszontelado-frissitese', 'OrderController@massUpdateReseller');
        Route::get('/megrendelesek/bevetelek/frissites', 'OrderController@updateOrderIncomes');

        Route::post('/api/billingo/test', 'UserController@testBillingo');

        Route::get('/dokumentumok/uj', 'DocumentController@create');
        Route::post('/dokumentumok/feltoltes', 'DocumentController@store');
        Route::get('/dokumentumok/{id}/torles', 'DocumentController@deleteDocument');

        Route::post('/bejegyzesek/kep-feltoltes', 'PostController@handleUpload');

        Route::get('/termekek', 'TrialProductController@listProducts');
        Route::post('/api/termek/atkapcsol/{sku}', 'TrialProductController@toggleProduct');
        Route::post('/termekek/szerkesztes', 'TrialProductController@editProduct');

        Route::get('/termekek/csomagok', 'BundleController@index');
        Route::get('/termekek/csomagok/uj', 'BundleController@create');
        Route::get('/termekek/csomagok/uj-resztermek', 'BundleController@row');
        Route::post('/termekek/csomagok/mentes', 'BundleController@store');
        Route::put('/termekek/csomagok/{bundleSku}/frissites', 'BundleController@update');
        Route::delete('/termekek/csomagok/{bundleSku}/torles', 'BundleController@destroy');
        Route::get('/termekek/csomagok/{bundleSku}', 'BundleController@edit');

        Route::get('keszlet/uj-keszlet-sor', 'StockController@createRow');
        Route::get('kozpont/keszlet', 'CentralStockController@index');
        Route::get('kozpont/keszlet/uj-sor', 'CentralStockController@getCentralStockRow');
        Route::post('kozpont/keszlet/hozzaadas', 'CentralStockController@store');
        Route::get('kozpont/keszlet/viszontelado/uj-sor', 'CentralStockController@getResellerStockRow');
        Route::post('kozpont/keszlet/viszontelado/feltoltes', 'CentralStockController@addStockToReseller');

        Route::get('kozpont/keszlet/letrehozas', 'StockController@create');
        Route::get('kozpont/keszlet/{userId}/lekerdezes', 'StockController@fetch');
        Route::get('kozpont/keszlet/{userId}/szerkesztes', 'StockController@edit');
        Route::put('kozpont/keszlet/{userId}/frissites', 'StockController@update');

        Route::get('kozpont/keszlet/hmtl', 'CentralStockController@stockHtml');

        Route::get('kozpont/penzugy', 'RevenueController@hqFinance');
        Route::get('api/kozpont/penzugy', 'RevenueController@getHqFinance');
        Route::post('kozpont/penzugy/bevetel/mentes', 'RevenueController@storeIncome');

        Route::get('kozpont/marketing', 'MarketingResultController@show');
        Route::post('kozpont/marketing/mentes', 'MarketingResultController@store');

        Route::get('/riportok/ujra-generalas', 'ReportController@regenerateReports');

        // Régió
        Route::get('regiok/generalas', 'RegionController@generateByResellers');
        Route::resource('regiok', 'RegionController');
    });

    // Index
    Route::get('/', 'UserController@home');

    // Fiók
    Route::get('/fiok', 'UserController@profile');
    Route::post('/fiok/jelszovaltas', 'UserController@updatePassword');

    // Pénzügyi rész
    Route::get('/api/bevetel', 'RevenueController@fetchIncome');
    Route::get('/api/kiadas/{expenseId}/torles', 'RevenueController@destroyExpense');
    Route::get('/api/kiadas', 'RevenueController@fetchExpense');
    Route::get('/benji-penz', 'BenjiMoneyController@getData')->middleware('admin');
    Route::post('/benji-penz/mentes', 'BenjiMoneyController@store')->middleware('admin');
    Route::get('/penzugy', 'RevenueController@income');
    Route::post('/kiadas/mentes', 'RevenueController@storeExpense');
    Route::get('/kiadas', 'RevenueController@expense');

    Route::resource('bejegyzesek', 'PostController');
    Route::get('hirek/{postId}', 'PostController@showPublic');

    // Régi URL...
    Route::get('/bevetel', function () {
        return redirect(action('RevenueController@income'));
    });

    // Megrendelések
    Route::get('/megrendelesek', 'OrderController@index');
    Route::post('/megrendelesek/allapot/frissites', 'OrderController@updateStatus');
    Route::post('/megrendelesek/tomeges/allapot/frissites', 'OrderController@massUpdateStatus');
    Route::get('/megrendelesek/{orderId}/statusz', 'OrderController@showStatus');
    Route::get('/megrendelesek/{orderId}', 'OrderController@show');
    Route::post('/megrendelesek/teljesites', 'OrderController@completeOrder');
	
	//Javascript számára megjegyzéseket küld vissza, HTML/Text.
	Route::get('megrendelesek/{orderID}/megjegyzesek/html', 'OrderController@getCommentsHTML');

    // Munkalapos dolgok
    Route::post('/munkalap/hozzaadas', 'WorksheetController@add');
    Route::post('/munkalap/torles', 'WorksheetController@remove');

    // Szállítólevél
    Route::post('/szallitolevel/letoltes', 'DocumentController@download');

    // Dokumentumok
    Route::get('/dokumentumok', 'DocumentController@index');
    Route::get('/dokumentumok/{id}/letoltes', 'DocumentController@getDocument');

    // Megjegyzések
    Route::post('/megrendelesek/megjegyzesek/mentes', 'OrderCommentController@store');
    Route::get('/megjegyzesek/{commentId}/szerkesztes', 'OrderCommentController@edit');
    Route::post('/megjegyzesek/frissites', 'OrderCommentController@update');
    Route::delete('/megjegyzesek/{commentId}/torles', 'OrderCommentController@destroy');

    // Teendők
    Route::get('/teendok', 'OrderTodoController@index');
    Route::get('/teendok/uj', 'OrderTodoController@create');
    Route::post('/teendok/mentes', 'OrderTodoController@store');
    Route::get('/teendok/{todoId}/szerkesztes', 'OrderTodoController@edit');
    Route::post('/teendok/frissites', 'OrderTodoController@update');
    Route::get('/teendok/{todoId}/kapcsolas', 'OrderTodoController@toggle');
    Route::delete('/teendok/{todoId}/torles', 'OrderTodoController@destroy');

    // Riportok
    Route::get('/riport/aktualis', 'ReportController@showQuick');
    Route::get('/riport/havi', 'ReportController@showMonthly');

    // Készlet
    Route::resource('keszletem', 'StockController', [
        'only' => [
            'index',
            'store',
        ],
    ]);
});

Route::post('/api/megrendeles/uj/{privateKey}', 'ShoprenterController@handleWebhook');
Route::post('/api/allapot-valtozas/{privateKey}', 'OrderStatusController@handleStatusWebhook');
Route::get('/api/havi-riportok/generalas/{privateKey}', 'ReportController@generateMonthlyReports');
Route::get('/megrendelesek/frissites/{privateKey}', 'ShoprenterController@updateOrders');
Route::get('/megrendelesek/bevetelek/frissites/{privateKey}', 'RevenueController@generateOrderIncomes');
Route::get('/statuszok/frissites/{privateKey}', 'OrderStatusController@updateStatuses');
Route::get('/termekek/frissites/{privateKey}', 'ShoprenterController@updateProducts');
Route::get('/test-billingo', 'ShoprenterController@testBillingo');
Route::get('/test-shoprenter', 'ShoprenterController@testShoprenter');
Route::post('/sr/termek-lekerdezes', 'ShoprenterController@getProduct');
Route::get('/api/iranyitoszam/ellenorzes', 'ShoprenterController@checkZip');

/**
 * Runs database migrations
 */
Route::get("/migrate/{secret}", function ($secret) {
	if ($secret != env("MAINTENANCE_TOKEN")) {
		abort(403, "Invalid maintenance token.");
	}

	Artisan::call('migrate', ['--force' => true]);

	return redirect('/')->with([
		'success' => 'Migráció sikeresen lefuttatva!',
	]);
});
