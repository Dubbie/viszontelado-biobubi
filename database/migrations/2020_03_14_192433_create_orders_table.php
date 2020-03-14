<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('postcode')->comment('Szállítási irányítószám (ez alapján szűrjük a megrendeléseket)');
            $table->string('inner_id')->comment('Belső azonosító a Shoprenteren belül');
            $table->string('inner_resource_id')->comment('Belső erőforrás azonosító a Shoprenteren belül');
            $table->integer('total')->comment('Teljes összeg ÁFA nélkül');
            $table->integer('total_gross')->comment('Teljes összeg + ÁFA');
            $table->integer('tax_price')->comment('ÁFA');
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
        Schema::dropIfExists('orders');
    }
}
