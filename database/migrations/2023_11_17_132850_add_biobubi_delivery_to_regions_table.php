<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBiobubiDeliveryToRegionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('regions', function (Blueprint $table) {
			$table->boolean('biobubi_delivery')->default(false);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('regions', function (Blueprint $table) {
			$table->dropColumn('biobubi_delivery');
		});
	}
}
