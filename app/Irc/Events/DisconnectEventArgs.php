<?php

/**
 * This file part of Nymph.
 * 
 * Copyright (c) 2012 Vaclav Vrbka (aurielle@aurielle.cz)
 * 
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nymph\Irc\Events;

use Nymph, Nette;



class DisconnectEventArgs extends Nymph\Events\EventArgs
{
	/** @var Nymph\Irc\Bot */
	private $bot;


	public function __construct(Nymph\Irc\Bot $bot)
	{
		$this->bot = $bot;
	}


	public function getBot()
	{
		return $this->bot;
	}
}