<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('customer_calls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('Viszonteladó azonosítója');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedBigInteger('customer_id')->comment('Ügyfél azonosítója');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->dateTime('due_date');
            $table->dateTime('called_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('customer_calls');
    }
}
