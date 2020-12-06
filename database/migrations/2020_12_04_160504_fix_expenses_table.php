<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('amount');
            $table->string('name')->comment('Kiadás megnevezése')->change();
            $table->unsignedFloat('gross_value')->comment('Bruttó összeg');
            $table->unsignedFloat('tax_value')->default(0)->comment('ÁFA tartalma');
            $table->text('comment')->nullable()->comment('Megjegyzés a kiadáshoz');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Viszonteladó akihez a kiadás tartozik (Üres esetén központ)')->change();
            $table->date('date')->useCurrent()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('gross_value');
            $table->dropColumn('tax_value');
            $table->integer('amount');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->date('date')->change();
            $table->dropColumn('comment');
        });
    }
}
