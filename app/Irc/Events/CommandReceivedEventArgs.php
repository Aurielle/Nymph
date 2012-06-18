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



class CommandReceivedEventArgs extends Nymph\Events\EventArgs
{
	/** @var array */
	private $data = array();

	/** @var string */
	private $raw = '';

	/** @var Nymph\Irc\Bot */
	private $bot;


	public function __construct(array $data, $raw, Nymph\Irc\Bot $bot)
	{
		$this->data = $data;
		$this->raw = $raw;
		$this->bot = $bot;
	}


	public function getBot()
	{
		return $this->bot;
	}


	public function getData()
	{
		return $this->data;
	}


	public function getRaw()
	{
		return $this->raw;
	}
}