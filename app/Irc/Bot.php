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
	/** @var Nymph\Events\EventManager */
	protected $eventManager;

	/** @var array */
	protected $params;

	/** @var resource */
	protected $socket;



	public function __construct(Nymph\Events\EventManager $evm, array $params)
	{
		$this->eventManager = $evm;
		$this->params = $params;
		$this->socket = fsockopen($this->params['server'], $this->params['port']);
		stream_set_blocking($this->socket, FALSE);
		$this->eventManager->dispatchEvent(Events::connect, new Events\ConnectEventArgs($this));

		// check for exist
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
		echo "--> $data\n"; // to do - log
		// todo detekce QUIT
	}


	private function non_block_read($fd, &$data) {
	    $read = array($fd);
	    $write = array();
	    $except = array();
	    $result = stream_select($read, $write, $except, 0);
	    if($result === false) throw new \Exception('stream_select failed');
	    if($result === 0) return false;
	    $data = fread($fd, 512);
	    return true;
	}



	protected function login($ident, $user, $nick, $password = NULL)
	{
		$this->sendData("USER $ident aurielle.cz $nick :$user");
		$this->sendData("NICK $nick");

		if ($password) {
			$this->sendData("PRIVMSG NickServ identify $password");
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
		//$this->onDisconnect($this, $reason);
		$this->sendData("QUIT $reason");
		exit;
	}


	public function run()
	{
		$loop = new Nymph\Tools\EndlessLoopIterator;
		$input = '';
		foreach ($loop as $iteration) {
			$data = fgets($this->socket, 512);
			$ex = explode(' ', $data);

			$this->eventManager->dispatchEvent(Events::commandReceived, new Events\CommandReceivedEventArgs($ex, $data, $this));

			if ($data) echo "<-- $data\n";

			/*if ($ex[0] == 'PING') {
				$this->sendData("PONG $ex[1]");
			}*/

			if (strpos($data, "\x01VERSION") !== FALSE) {
				$this->sendData("NOTICE " . preg_replace('#\:(.+)\!.+#', '$1', $ex[0]) . " :\x01VERSION Aurielle/Nymph v1.0-dev\x01");
			}

			if ($this->non_block_read(STDIN, $input)) {
				$this->sendData($input);
			}

			// reduce CPU usage, 2 iterations per second are more than enough
			usleep(500);
		}
	}
}