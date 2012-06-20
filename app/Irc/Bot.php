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

	/** @var bool */
	protected $stop = FALSE;

	/** @var string */
	protected $currentNick;



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
		// We can't send data if already disconnected
		if (!(is_resource($this->socket) && get_resource_type($this->socket) === 'stream')) {
			return;
		}

		$this->eventManager->dispatchEvent(Events::commandSent, new Events\CommandSentEventArgs($data, $this));

		// QUIT detection
		if (Nette\Utils\Strings::startsWith($data, 'QUIT')) {
			$this->eventManager->dispatchEvent(Events::disconnect, new Events\DisconnectEventArgs($this));
			$this->stop = TRUE;
		}

		// NICK detection
		if (Nette\Utils\Strings::startsWith($data, 'NICK')) {
			$tmp = explode(' ', $data);
			$this->currentNick = $tmp[1];
		}

		fputs($this->socket, $data . "\r\n");
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
		$this->sendData("QUIT :$reason");
	}


	public function nick($nick)
	{
		$this->sendData("NICK $nick");
	}


	public function run()
	{
		$loop = new Nymph\Tools\EndlessLoopIterator;
		$input = '';
		foreach ($loop as $iteration) {
			$data = fgets($this->socket, 512);
			$this->eventManager->dispatchEvent(Events::commandReceived, new Events\CommandReceivedEventArgs($data, $this));

			if ($this->stop || ($data && Nette\Utils\Strings::startsWith($data, 'ERROR :Closing Link'))) {
				fwrite(STDOUT, "[INFO] Nymph is shutting down.\n");
				fclose($this->socket);
				break;
			}

			if ($this->non_block_read(STDIN, $input)) {
				$this->sendData($input);
			}

			// reduce CPU usage, 2 iterations per second are more than enough
			usleep(500);
		}
	}



	public function getParams()
	{
		return $this->params;
	}


	public function getCurrentNick()
	{
		return $this->currentNick;
	}
}