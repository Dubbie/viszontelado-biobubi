<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        // Mix manifest
        if (array_key_exists('SERVER_ADDR', $_SERVER) && $_SERVER["SERVER_ADDR"] == "185.51.191.57") {
            $this->app->bind('path.public', function () {
                return base_path().'/../public_html';
            });
        }

        // Blade kiegészítés
        Blade::directive('money', function ($expression) {
            return "<?php echo '<span>' . resolve('App\Subesz\MoneyService')->getFormattedMoney({$expression}) . '</span>'; ?>";
        });

        Schema::defaultStringLength(191);

        // Bootstrap léptető modul
        Paginator::useBootstrap();
    }
}
