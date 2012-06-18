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
class IrcExtension extends CompilerExtension
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

	/** @var array */
	public $networkDefaults = array(
		'server' => NULL,
		'port' => 6667,
		'nick' => 'NymphBot',
		'password' => NULL,
		'alternativeNicks' => array(),
		'ident' => NULL,
		'user' => NULL,
		'channels' => array(),
	);

	/** @var array */
	private $networks = array();


	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		if (empty($config['networks'])) {
			throw new Nette\InvalidStateException('You have to define at least one IRC server.');
		}

		foreach ($config['networks'] as $name => $network) {
			$options = self::getOptions($network, $this->networkDefaults, TRUE);

			if (empty($options['server'])) {
				throw new Nette\InvalidStateException("Network '$name' has no server specified.");
			}

			if (empty($options['alternativeNicks'])) {
				$options['alternativeNicks'] = $config['alternativeNicks'];
			}

			if (empty($options['ident'])) {
				$options['ident'] = Nymph\Nymph::NAME;
			}

			if (empty($options['user'])) {
				$options['user'] = Nymph\Nymph::NAME . ' ' . Nymph\Nymph::VERSION;
			}

			$this->networks[$name] = $options;
		}

		// Select first network if no selected
		if (!$config['default']) {
			$config['default'] = reset(array_keys($config['networks']));
		}

		$bot = $container->addDefinition('bot')
				->setClass('Nymph\Irc\Bot', array('@eventManager', $this->networks[$config['default']]));
	}
}