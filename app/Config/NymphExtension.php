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
class NymphExtension extends CompilerExtension
{
	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$container->addDefinition('eventManager')
			->setClass('Nymph\Events\EventManager');
	}


	public function beforeCompile()
	{
		$this->registerEventSubscribers($this->getContainerBuilder());
	}



	/**
	 * @param \Nette\DI\ContainerBuilder $container
	 */
	protected function registerEventSubscribers(ContainerBuilder $container)
	{
		foreach ($container->findByTag('eventSubscriber') as $listener => $meta) {
			$container->getDefinition('eventManager')
				->addSetup('addEventSubscriber', array('@' . $listener));
		}
	}
}