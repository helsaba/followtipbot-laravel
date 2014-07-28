<?php

class Tipped extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'followers_tipped';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array();

	protected $guarded = array('id');
	protected $fillable = array('follower_id', 'follower_screen_name', 'follower_description', 'amount_tipped');

	public function __construct() {
	}

}
