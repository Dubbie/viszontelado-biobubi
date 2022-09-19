<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::dropIfExists('stocks');

        Schema::create('stocks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('sku');
            $table->bigInteger('inventory_on_hand');
            $table->timestamps();
        });

        $ss = resolve('App\Subesz\StockService');
        foreach ($ss->getBaseProducts() as $product) {
            $stock                    = new \App\Stock();
            $stock->sku               = $product->sku;
            $stock->inventory_on_hand = 0;
            $stock->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        foreach (\App\Stock::where('user_id', null)->get() as $stockEntry) {
            $stockEntry->delete();
        }

        Schema::dropIfExists('stocks');
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('sku');
            $table->bigInteger('inventory_on_hand');
            $table->timestamps();
        });
    }
}
