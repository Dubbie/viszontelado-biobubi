<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReducedValueToMoneyTransferOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('money_transfer_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('reduced_value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('money_transfer_orders', function (Blueprint $table) {
            $table->dropColumn('reduced_value');
        });
    }
}
