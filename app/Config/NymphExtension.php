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
 * NymphExtension
 */
class NymphExtension extends Nette\Config\CompilerExtension
{
	/** @var array */
	public $defaults = array(
		'commands' => array(),
	);


	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$bot = $container->getDefinition('irc.bot');
		foreach ($config['commands'] as $cmd) {
			$app->addSetup('addCommand', $cmd);
		}
	}
}