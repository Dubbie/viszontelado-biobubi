<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeStatusNullableOnOrderTodosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_todos', function (Blueprint $table) {
            $table->string('status_text')->nullable(true)->change();
            $table->string('status_color')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_todos', function (Blueprint $table) {
            $table->string('status_text')->nullable(false)->change();
            $table->string('status_color')->nullable(false)->change();
        });
    }
}
