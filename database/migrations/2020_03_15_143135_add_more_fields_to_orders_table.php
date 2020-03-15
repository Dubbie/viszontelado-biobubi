<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreFieldsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('postcode');

            $table->string('shipping_postcode')->comment('Szállítási irányítószám (ez alapján szűrjük a megrendeléseket)');
            $table->string('shipping_city')->comment('Szállítási város');
            $table->string('shipping_address')->comment('Szállítási cím');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('shipping_postcode');
            $table->dropColumn('shipping_city');
            $table->dropColumn('shipping_address');

            $table->string('postcode')->comment('Szállítási irányítószám (ez alapján szűrjük a megrendeléseket)');
        });
    }
}
