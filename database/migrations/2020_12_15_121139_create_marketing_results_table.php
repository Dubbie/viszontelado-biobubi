<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketingResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketing_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->comment('Akihez tartozik a marketinges eredmény');
            $table->foreign('user_id')->references('id')->on('users');
            $table->float('old_balance')->comment('A viszonteladó egyenlege a mentés előtt');
            $table->float('topup_amount')->default(0)->comment('Az összeg amennyi feltöltésre kerül a viszonteladó egyenlegére');
            $table->float('spent')->comment('Az összeg amennyit elköltöttek marketingre');
            $table->bigInteger('reached')->comment('Ennyi embert értek el a hírdetéssel');
            $table->text('comment')->nullable();
            $table->date('date')->comment('Ez a dátum mutatja, hogy melyik hónapra vonatkozik a riport');
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
        Schema::dropIfExists('marketing_results');
    }
}
