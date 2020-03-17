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

Route::group(['middleware' => 'auth'], function() {
    /**
     * Adminisztrátori jogot igénylő routeok.
     */
    Route::group(['middleware' => 'admin'], function() {
        Route::get('/felhasznalok', 'UserController@index');
        Route::get('/felhasznalok/uj', 'UserController@create');
        Route::post('/felhasznalok/mentes', 'UserController@store');
        Route::get('/felhasznalok/{userId}/megrendelesek', 'UserController@orders');
        Route::get('/felhasznalok/{userId}/szerkesztes', 'UserController@edit');
        Route::put('/felhasznalok/{userId}/frissites', 'UserController@update');
        Route::get('/felhasznalok/{userId}', 'UserController@show');
    });

    Route::get('/', function () {
        return view('home');
    });

    Route::get('/fiok', 'UserController@profile');
    Route::post('/fiok/jelszovaltas', 'UserController@updatePassword');

    Route::get('/bevetel', 'RevenueController@income');

    Route::get('/megrendelesek', 'OrderController@index');
    Route::post('/megrendelesek/allapot/frissites', 'OrderController@updateStatus');
    Route::get('/megrendelesek/{orderId}/statusz', 'OrderController@showStatus');
    Route::get('/megrendelesek/{orderId}', 'OrderController@show');
});

Route::post('/api/megrendeles/uj/{privateKey}', 'OrderController@handleWebhook');
Route::get('/megrendelesek/frissites/{privateKey}', 'ShoprenterController@updateOrders');
/**
 * Runs database migrations
 */
Route::get("/migrate/{secret}", function ($secret) {
    if ($secret != env("MAINTENANCE_TOKEN")) {
        abort(403, "Invalid maintenance token.");
    }
    echo "DB maintenance starts <br>";
    echo Artisan::call('migrate', ['--force' => true]);
    echo "DB maintenance Over";
});