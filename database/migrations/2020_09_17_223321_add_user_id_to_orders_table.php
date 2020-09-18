<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /** @var \App\Subesz\OrderService $os */
        $os = resolve('App\Subesz\OrderService');
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('reseller_id')->default(env('ADMIN_USER_ID'));
            $table->foreign('reseller_id')->references('id')->on('users');
        });

        foreach (\App\Order::all() as $order) {
            $order->reseller_id = $os->getResellerByZip($order->shipping_postcode)->id;
            $order->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('reseller_id');
        });
    }
}
