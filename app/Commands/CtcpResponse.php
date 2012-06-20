<?php

/**
 * This file part of Nymph.
 * 
 * Copyright (c) 2012 Vaclav Vrbka (aurielle@aurielle.cz)
 * 
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nymph\Commands;

use Nymph, Nette;



/**
 * Responding to CTCP commands
 */
class CtcpResponse extends Nette\Object implements Nymph\Events\EventSubscriber
{
	public function getSubscribedEvents()
	{
		return array(Nymph\Irc\Events::commandReceived);
	}


	public function commandReceived(Nymph\Irc\Events\CommandReceivedEventArgs $args)
	{
		if ($matches = Nette\Utils\Strings::match(trim($args->raw), '#\:([^!]+)\![^ ]+ PRIVMSG ' . preg_quote($args->bot->getCurrentNick(), '#') . " :\x01(VERSION|PING|TIME|SOURCE|FINGER) ?(.+)?\x01#")) {
			$this->{strtolower($matches[2])}($args, $matches);
		}
	}



	protected function version(Nymph\Irc\Events\CommandReceivedEventArgs $args, $matches)
	{
		$args->bot->sendData("NOTICE $matches[1] :\x01VERSION " . Nymph\Nymph::NAME . " " . Nymph\Nymph::VERSION . "\x01");
	}

	protected function ping(Nymph\Irc\Events\CommandReceivedEventArgs $args, $matches)
	{
		$args->bot->sendData("NOTICE $matches[1] :\x01PING " . time() . "\x01");
	}

	protected function time(Nymph\Irc\Events\CommandReceivedEventArgs $args, $matches)
	{
		$args->bot->sendData("NOTICE $matches[1] :\x01TIME " . date('r') . "\x01");
	}

	protected function source(Nymph\Irc\Events\CommandReceivedEventArgs $args, $matches)
	{
		$args->bot->sendData("NOTICE $matches[1] :\x01SOURCE https://github.com/Aurielle/Nymph\x01");
	}

	protected function finger(Nymph\Irc\Events\CommandReceivedEventArgs $args, $matches)
	{
		$args->bot->sendData("NOTICE $matches[1] :\x01FINGER Nymph, IRC bot driven by PHP, Nette Framework and open-source code.\x01");
	}
}