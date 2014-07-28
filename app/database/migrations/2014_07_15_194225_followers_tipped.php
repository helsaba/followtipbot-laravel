<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FollowersTipped extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('followers_tipped', function($t) {
			$t->increments('id');
			$t->string('follower_id');
			$t->string('follower_screen_name');
			$t->string('follower_description');
			$t->float('amount_tipped');
			$t->timestamp('updated_at');
			$t->timestamp('created_at');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('followers_tipped');
	}

}
