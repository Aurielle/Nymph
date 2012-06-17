<?php

/**
 * This file part of Nymph.
 * 
 * Copyright (c) 2012 Vaclav Vrbka (aurielle@aurielle.cz)
 * 
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nymph\Irc;

use Nymph, Nette;



/**
 * Bot
 */
class Bot extends Nette\Object
{
	/** @var array */
	protected $params;

	/** @var resource */
	protected $socket;


	/** @var array */
	public $onConnect;

	/** @var array */
	public $onDisconnect;

	/** @var array */
	public $onLogin;

	/** @var array */
	public $onCommandReceived;

	/** @var array */
	public $onCommandSent;



	public function __construct(array $params)
	{
		$this->params = $params;
		$this->socket = fsockopen($this->params['server'], $this->params['port']);
		$this->onConnect($this);

		$this->login($this->params['ident'], $this->params['user'], $this->params['nick'], $this->params['password']);
		$this->joinChannels($this->params['channels']);
	}


	public function __destruct()
	{
		$this->quit('Killed from console');
	}


	public function sendData($data)
	{
		fputs($this->socket, $data . "\r\n");
		echo "Sending data: $data\n"; // to do - log
	}


	private function non_block_read($fd, &$data) {
	    $read = array($fd);
	    $write = array();
	    $except = array();
	    $result = stream_select($read, $write, $except, 0);
	    if($result === false) throw new \Exception('stream_select failed');
	    if($result === 0) return false;
	    $data = stream_get_line($fd, 1);
	    return true;
	}



	protected function login($ident, $user, $nick, $password = NULL)
	{
		$this->sendData("USER $ident aurielle.cz $nick :$user");
		$this->sendData("NICK $nick");

		if ($password) {
			$this->sendData("MSG NickServ identify $password");
		}
	}


	protected function joinChannels(array $channels)
	{
		foreach ($channels as $chan) {
			$chan = trim($chan, '#');
			$this->sendData("JOIN #$chan");
		}
	}


	public function quit($reason = 'Leaving')
	{
		$this->onDisconnect($this, $reason);
		$this->sendData("QUIT $reason");
		exit();
	}


	public function run()
	{
		$stdin = fopen('php://stdin', 'r');
		$read = array($stdin);
		$write = array();
		$except = array();
		stream_select($read, $write, $except, 0);

		while (TRUE) {
			$input = fread($stdin, 128);
			$data = fgets($this->socket, 256);
			$ex = explode(' ', $data);
			echo "Received: $data\n";

			if ($ex[0] == 'PING') {
				$this->sendData("PONG $ex[1]");
			}

			if (strpos($data, 'VERSION') !== FALSE) {
				dump($ex);
				dump(trim($ex[3]));
				$this->sendData("NOTICE " . preg_replace('#\:(.+)\!.+#', '$1', $ex[0]) . " :\001VERSION Nymph : v1.0dev : NetteFw");
			}

			if ($input) {
				dump($input);
			}
		}
	}
}