<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('report_id')->comment('A riport amihez csatlakozunk');
            $table->foreign('report_id')->references('id')->on('reports');
            $table->string('product_sku')->comment('Eladott termék azonosítója');
            $table->foreign('product_sku')->references('sku')->on('products');
            $table->unsignedInteger('product_qty');
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
        Schema::dropIfExists('report_products');
    }
}
