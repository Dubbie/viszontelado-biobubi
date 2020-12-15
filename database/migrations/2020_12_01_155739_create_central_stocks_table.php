<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCentralStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('central_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('product_sku');
            $table->foreign('product_sku')->references('sku')->on('products')->onDelete('cascade');
            $table->bigInteger('inventory_on_hand');
            $table->timestamps();
        });

        $ss = resolve('App\Subesz\StockService');
        foreach ($ss->getBaseProducts() as $product) {
            $stock = new \App\CentralStock();
            $stock->product_sku = $product->sku;
            $stock->inventory_on_hand = 0;
            $stock->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('central_stocks');
    }
}
