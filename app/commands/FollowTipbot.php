<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class FollowTipbot extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'tipbot:followers';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Tip your followers!';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$DEBUG = $this->option('debug');
		$LIVE = $this->option('live');
		$guzzle = new GuzzleHttp\Client();

		$creds = Twitter::getCredentials();
		$this->info('Connected as @' . $creds->screen_name .' ('.$creds->id.')');
		if ($this->argument('fresh')) {
			$followers = Twitter::getFollowersIds(array('screen_name' => $creds->screen_name));
			foreach ($followers->ids as $id) {
				$fol = Twitter::getUsersLookup(array('user_id' => $id));
				if (is_null($id) || empty($fol) || sizeof($fol) == 0 || gettype($fol) != 'array') continue;

				// get amount tipped using @tipdoge API
				$tipped = Tipdoge::getAmountFromToUser($guzzle, $creds->id, $id);
				$tipped = json_decode($tipped->getBody());
				$amountTipped = (!$tipped->amount) ? 0 : $tipped->amount;

				$tipped = new Tipped;
				$tipped->follower_id = $id;
				$tipped->follower_screen_name = $fol[0]->screen_name;
				$tipped->follower_description = $fol[0]->description;
				$tipped->amount_tipped = $amountTipped;
				$tipped->save();

				sleep(30);
			}
			$followers = $followers->ids;
		} else {
			$followers = Tipped::all();
		}
		$this->info('# of followers: ' . count($followers));

		$toTip = $followers;
		//$this->info("\nretrieving amounts tipped to followers...\n");
		//$this->info('# of new followers: ' . count($toTip));

		$balance = $this->option('balance');
		if (empty($balance)) {
			$balance = Tipdoge::getBalance($guzzle, $creds->id);
			$balance = json_decode($balance->getBody());
			$balance = $balance->balance;
		}
		$this->info('Balance: ' . $balance);
		$tipAmount = $this->option('amount');
		if (empty($tipAmount)) {
			// distribute the DOGE evenly
			$tipAmount = floor($balance / count($toTip));
		}
		$this->info('Tip amount per follower: ' . $tipAmount);

		$i = 0;
		foreach ($toTip as $t) {
			if ($this->option('who') == 'shibes' && isset($t->follower_description)) {
				if (!preg_match_all('/(D[0-9A-Za-z]{33}|DOGE|Shibe|doge|shibe|shiba|Shiba)/', $t->follower_description, $matches) == 1) {
					//$this->info($t->follower_screen_name . ' - ' . $t->follower_description);
					continue;
				}
			}
			$tweet = $this->getMessage('tipdoge', 'tip', $t->follower_screen_name, $tipAmount, '#dogecoin');

				$i++;
			if ($LIVE) {
				$i++;
				if ($i % 10 == 0) sleep(120); // wait a 2 minutes between each round of 10
				Twitter::postTweet(array('status' => $tweet));
			}

			$this->info($tweet);
		}
		$this->info($i);
	}

	protected function getMessage($tipbot, $tipCommand, $tippedUser, $tipAmount, $coinHashtag) {
		$messages = array(
			'spreading #love & #goodvibes! keep it flowin\' #tothemoon',
			'#peace, #love, #harmony: #crypto changing the world!',
			'- the #happy-go-lucky #crypto community~ #peace & #charity',
			'- the #cryptocurrency enacting real change w/ #charity',
			'you are now inducted into this joyful #crypto community. #tothemoon',
			'#tothemoon is just a pit-stop for #worldpeace',
			'- the peaceful, joyful #crypto community spreading #goodvibes',
			'you are always welcome as a fellow #shibe. #peace!',
			'is an internet hug you can share, tip & spend. #howtodoge',
			'you are loved. please enjoy this #doge, #newshibe.',
			'we are headed #tothemoon & we have headroom in our rockets!',
			'a #shibe in need is a friend indeed.',
			'you now have cool #crypto monies! #tipitforward',
			'we want you to be #happy! may the #doge be with you.',
			'a #shibe\'s love is unconditional. let\'s go #tothemoon together!',
			'#peace in your heart, #doge in your paws, #love to the moon!',
			'we shibes say #WOW because.... WOW!! much #love',
			'we are trailblazing #tothemoon w/ #crypto, #crowdfunding & #charity',
			'all #shibes wish to be #happy - may you receive every benefit!',
			'let me tell you a story about #WOW pic.twitter.com/FPTagMTXaM',
			'- the trusted #cryptocurrency for your WOW needs pic.twitter.com/MKt856efcl',
			'we\'re going #tothemoon!!! (frrl) pic.twitter.com/QNlQqpa8Ht',
			'- the cute digital currency - much fun - WOW!! pic.twitter.com/v0JrUzzeQx',
			'is #love, #hope, #harmony, & hijinks! pic.twitter.com/2i3MBQZqRa',
			'WOW!! you can #tipitforward or buy goods/services pic.twitter.com/czMieDtkIH',
			'we\'re building a new model for internet economy! pic.twitter.com/l8RQNKSp7A',
			'- comrades of #bitcoin - friends of the world pic.twitter.com/hESGriBEVt',
			'is #love, #doge is life. peace, friend! pic.twitter.com/OtlOA53Mbp',
			'- known for random acts of WOWness pic.twitter.com/GgbbSh3avu',
			'hot off the presses! please share & enjoy pic.twitter.com/2LwflZzX6C',
		);

		$msg = sprintf('@%s %s @%s %s %s %s',
			$tipbot, $tipCommand, $tippedUser, $tipAmount, $coinHashtag,
			$messages[mt_rand(0, count($messages)-1)]
		);

		return $msg;
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('fresh', InputArgument::OPTIONAL, 'Get fresh follower data [default=no].'),
			array('distribute', InputArgument::OPTIONAL, 'Evenly distribute balance to followers [default=yes].'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('live', null, InputOption::VALUE_REQUIRED, 'We running live (actually tweet)? [default=no]', false),
			array('balance', null, InputOption::VALUE_OPTIONAL, 'Specify a balance to tip out.', null),
			array('amount', null, InputOption::VALUE_OPTIONAL, 'Specify an amount to tip out.', null),
			array('who', null, InputOption::VALUE_REQUIRED, 'Who to tip?', null),
			array('debug', null, InputOption::VALUE_OPTIONAL, 'Debug mode', false),
		);
	}

}
