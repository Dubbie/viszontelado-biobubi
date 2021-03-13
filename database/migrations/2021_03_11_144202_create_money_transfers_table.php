<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoneyTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('money_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('A user aki megkapja az utalt pénzt');
            $table->foreign('user_id')->references('id')->on('users');
            $table->float('amount')->comment('Az átutalás összege');
            $table->dateTime('completed_at')->nullable();
            $table->string('attachment_path')->nullable()->comment('Az utalásról szóló csatolmány');
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
        Schema::dropIfExists('money_transfers');
    }
}
