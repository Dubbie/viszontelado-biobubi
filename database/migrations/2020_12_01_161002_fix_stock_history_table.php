<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixStockHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('stock_histories');

        Schema::create('stock_movement', function (Blueprint $table) {
            $table->id();
            $table->string('product_sku')->comment('A termék amit kap');
            $table->foreign('product_sku')->references('sku')->on('products');
            $table->unsignedBigInteger('user_id')->comment('Viszonteladó aki kapja a készletet');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('quantity');
            $table->unsignedFloat('gross_price');
            $table->unsignedFloat('purchase_price');
            $table->unsignedFloat('wholesale_price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_movement');

        Schema::create('stock_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipient');
            $table->foreign('recipient')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('sender');
            $table->foreign('sender')->references('id')->on('users')->onDelete('cascade');
            $table->string('sku');
            $table->string('name');
            $table->bigInteger('amount');
            $table->timestamps();
        });
    }
}
