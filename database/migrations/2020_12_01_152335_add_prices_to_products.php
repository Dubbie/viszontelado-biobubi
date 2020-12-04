<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPricesToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedFloat('purchase_price')->default('1')->comment("Beszerzési ár");
            $table->unsignedFloat('wholesale_price')->default('1')->comment("Nagyker ár");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('purchase_price');
            $table->dropColumn('wholesale_price');
        });
    }
}
