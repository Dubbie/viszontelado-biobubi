<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Bevétel megnevezése');
            $table->unsignedFloat('gross_value')->comment('Bruttó összeg');
            $table->unsignedFloat('tax_value')->default(0)->comment('ÁFA tartalma');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Viszonteladó aki kapja a készletet');
            $table->foreign('user_id')->references('id')->on('users');
            $table->text('comment')->comment('Megjegyzés a bevételhez');
            $table->date('date')->useCurrent();
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
        Schema::dropIfExists('incomes');
    }
}
