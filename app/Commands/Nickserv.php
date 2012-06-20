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
 * Nickserv nick managment
 */
class Nickserv extends Nette\Object implements Nymph\Events\EventSubscriber
{
	/** @var array */
	private $alternativeNicks;



	public function getSubscribedEvents()
	{
		return array(Nymph\Irc\Events::commandReceived);
	}


	public function commandReceived(Nymph\Irc\Events\CommandReceivedEventArgs $args)
	{
		if (empty($this->alternativeNicks)) {
			$this->alternativeNicks = $args->bot->params['alternativeNicks'] + array($args->bot->params['nick'] . rand(0,1000));
		}

		if (Nette\Utils\Strings::match(trim($args->raw), '#\:NickServ\![^ ]+ NOTICE ' . preg_quote($args->bot->getCurrentNick(), '#') . ' :This nickname is registered and protected#i')) {
			$args->bot->nick(sprintf(array_shift($this->alternativeNicks), $args->bot->params['nick']));
		}
	}
}