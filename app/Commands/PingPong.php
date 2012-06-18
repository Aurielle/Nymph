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



class PingPong extends Nette\Object implements Nymph\Events\EventSubscriber
{
	public function getSubscribedEvents()
	{
		return array(Nymph\Irc\Events::commandReceived);
	}


	public function commandReceived(Nymph\Irc\Events\CommandReceivedEventArgs $args)
	{
		if ($args->data[0] === 'PING') {
			$args->bot->sendData("PONG {$args->data[1]}");
		}
	}
}