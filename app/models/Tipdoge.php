<?php

class Tipdoge extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = '';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array();

	public function __construct() {
	}

	static public function getBalance($guzzle, $twitterId) {
		return $guzzle->get('http://tipdoge.info/api/?q=getbalance&apikey=&id='.$twitterId);
	}

	static public function getAmountFromToUser($guzzle, $from, $to) {
		return $guzzle->get('http://tipdoge.info/api/?q=getAmountFromToUser&apikey=&user_from='.$from.'&user_to='.$to);
	}

}
