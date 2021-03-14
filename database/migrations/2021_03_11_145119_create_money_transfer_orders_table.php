<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoneyTransferOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('money_transfer_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transfer_id')->comment('Az átutalás azonosítója');
            $table->foreign('transfer_id')->references('id')->on('money_transfers')->cascadeOnDelete();
            $table->unsignedBigInteger('order_id')->comment('A kiválasztott megrendelés');
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
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
        Schema::dropIfExists('money_transfer_orders');
    }
}
