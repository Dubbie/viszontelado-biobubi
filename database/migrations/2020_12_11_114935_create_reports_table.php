<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('Viszonteladó akihez tartozik a riport.');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedInteger('delivered_orders')->comment('Kiszállított megrendelések');
            $table->unsignedFloat('gross_income')->comment('Bruttó bevétel a címekből');
            $table->unsignedFloat('gross_expense')->comment('Bruttó kiadás');
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
        Schema::dropIfExists('reports');
    }
}