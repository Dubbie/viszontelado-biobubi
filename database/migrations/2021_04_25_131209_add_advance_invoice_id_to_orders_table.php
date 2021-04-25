<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdvanceInvoiceIdToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('advance_invoice_id')->nullable()->comment('Előleg számla azonosítója');
            $table->string('advance_invoice_path')->nullable()->comment('Előleg számla letöltési helye');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('advance_invoice_id');
            $table->dropColumn('advance_invoice_path');
        });
    }
}
