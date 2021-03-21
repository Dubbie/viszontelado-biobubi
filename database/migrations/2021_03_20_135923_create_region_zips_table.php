<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegionZipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('region_zips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('region_id')->nullable()->comment('A régió azonosítója');
            $table->foreign('region_id')->references('id')->on('regions')->cascadeOnDelete();
            $table->string('zip');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('region_zips');
    }
}
