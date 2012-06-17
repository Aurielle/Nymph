<?php

/**
 * This file part of Nymph.
 * 
 * Copyright (c) 2012 Vaclav Vrbka (aurielle@aurielle.cz)
 * 
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nymph\Config;

use Nymph, Nette;



/**
 * IrcExtension
 */
class IrcExtension extends Nette\Config\CompilerExtension
{
	/** @var array */
	public $defaults = array(
		'networks' => array(),
		'default' => NULL,
		'alternativeNicks' => array(
			'%s`',
			'%s_',
			'%s|',
		),
	);


	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		if (!$config['default']) {
			throw new Nette\InvalidStateException('Default network not selected.');
		}

		$bot = $container->addDefinition($this->prefix('bot'))
				->setClass('Nymph\Irc\Bot', array($config['networks'][$config['default']]));
	}
}