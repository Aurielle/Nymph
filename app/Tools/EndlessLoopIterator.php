<?php

/**
 * This file part of Nymph.
 * 
 * Copyright (c) 2012 Vaclav Vrbka (aurielle@aurielle.cz)
 * 
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nymph\Tools;

use Nymph, Nette;



/**
 * Endless loop iterator
 */
class EndlessLoopIterator implements \Iterator
{
	/** @var int */
	private $position;


	public function rewind()
	{
		$this->position = 0;
	}

	public function current()
	{
		return $this->position;
	}

	public function key()
	{
		return $this->position;
	}

	public function next()
	{
		++$this->position;
	}

	public function valid()
	{
		return TRUE;
	}
}