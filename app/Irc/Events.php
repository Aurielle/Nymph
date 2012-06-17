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
 * Events
 */
final class Events extends Nette\Object
{
	public class __construct()
	{
		throw new Nette\StaticClassException;
	}



	const commandReceived = 'commandReceived';

	const commandSent = 'commandSent';
}