<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FreeTestsColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		//Add column for free tests
		Schema::table('test_types', function(Blueprint $table)
		{
			$table->integer('orderable_test');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//Reverse up
		$table->dropColumn('orderable_test');
	}

}
