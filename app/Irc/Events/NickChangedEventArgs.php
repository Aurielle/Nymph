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



class NickChangedEventArgs extends Nymph\Events\EventArgs
{
	/** @var Nymph\Irc\Bot */
	private $bot;

	/** @var string */
	private $newNick;

	/** @var string */
	private $oldNick;


	public function __construct($newNick, $oldNick, Nymph\Irc\Bot $bot)
	{
		$this->bot = $bot;
		$this->newNick = $newNick;
		$this->oldNick = $oldNick;
	}


	public function getBot()
	{
		return $this->bot;
	}


	public function getNewNick()
	{
		return $this->newNick;
	}


	public function getOldNick()
	{
		return $this->oldNick;
	}
}