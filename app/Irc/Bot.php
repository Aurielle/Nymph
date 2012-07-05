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

	/** @var bool */
	protected $connecting = FALSE;


	/** @var string */
	protected $currentNick;

	/** @var string */
	protected $server;


	const	WELCOME = 1,
	     	NICKNAME_IN_USE = 433,
	     	REGISTER_FIRST = 451;



	public function __construct(Nymph\Events\EventManager $evm, array $params)
	{
		$this->eventManager = $evm;
		$this->params = $params;
		$this->socket = fsockopen($this->params['server'], $this->params['port']);
		stream_set_blocking($this->socket, FALSE);

		$this->connecting = TRUE;
		$this->login($this->params['ident'], $this->params['user'], $this->params['nick'], $this->params['password']);
		$this->run();
		
		$this->joinChannels($this->params['channels']);
		$this->eventManager->dispatchEvent(Events::connect, new Events\ConnectEventArgs($this));
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
			$this->eventManager->dispatchEvent(Events::nickChanged, new Events\NickChangedEventArgs($tmp[1], $this->currentNick, $this));

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
		$this->nick($nick);

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
		if ($this->stop) {
			return;
		}

		$this->sendData("QUIT :$reason"); // this only sends the termination command, but we need the reply too
		$this->run();
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

			// Process connection
			if ($this->connecting) {
				if (!$data) {
					continue;
				}

				if (Nette\Utils\Strings::startsWith($data, 'ERROR :Closing Link')) {
					fwrite(STDOUT, "[ERROR] An error occured, shutting down.\n");
					fclose($this->socket);
					return;
				}

				$tmp = explode(' ', trim($data));
				switch ((int) $tmp[1]) {
					case self::WELCOME:
						$this->connecting = FALSE;
						return;

					case self::NICKNAME_IN_USE:
						if (empty($this->params['alternativeNicks'])) {
							$this->quit('No more alternative nicks.');
						}

						$this->nick(sprintf(array_shift($this->params['alternativeNicks']), $this->params['nick']));
						break;

					case self::REGISTER_FIRST:
						break;	// just wait

					default:
						break;	// ignored command
				}

				continue;	// don't execute any further
			}

			// Stopping conditions
			if ($this->stop && ($data && Nette\Utils\Strings::startsWith($data, 'ERROR :Closing Link'))) {
				fwrite(STDOUT, "[INFO] Nymph is shutting down.\n");
				fclose($this->socket);
				break;
			}

			// Read from STDIN in order to process commands
			if ($this->non_block_read(STDIN, $input)) {
				$this->sendData($input);
			}

			// Reduce CPU usage, 2 iterations per second are more than enough
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