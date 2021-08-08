<?php

namespace App\Providers;

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
        if (array_key_exists('REMOTE_ADDR', $_SERVER) && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
            $this->app->bind('path.public', function () {
                return base_path().'/../public_html';
            });
        }

        // Blade kiegészítés
        Blade::directive('money', function ($expression) {
            return "<?php echo '<span>' . resolve('App\Subesz\MoneyService')->getFormattedMoney({$expression}) . '</span>'; ?>";
        });

        Schema::defaultStringLength(191);
    }
}
