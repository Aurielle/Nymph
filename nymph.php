<?php

/**
 * This file is part of Nymph.
 * 
 * Copyright (c) 2012 Vaclav Vrbka (aurielle@aurielle.cz)
 * 
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

if (PHP_SAPI !== 'cli') {
	die('<h1>Please run Nymph from the command line.</h1>');
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/nette/nette/Nette/loader.php';

define('NYMPH_ROOT', __DIR__);
declare(ticks = 1);

// Configuration
$configurator = new Nette\Config\Configurator();

// Causes shutdown handler to be called normally when the script is exitted via ctrl+c
// works only on linux and PHP compiled with --enable-pcntl
if (function_exists('pcntl_signal')) {
	$terminate = function() {
		exit();
	};

	pcntl_signal(SIGINT, $terminate);
	pcntl_signal(SIGTERM, $terminate);
}

// Error visualization & logging
$configurator->setDebugMode(TRUE);
$configurator->enableDebugger(__DIR__ . '/log/error');

// Autoloader and cache
$configurator->setTempDirectory(__DIR__ . '/temp');
$configurator->createRobotLoader()
	->addDirectory(__DIR__ . '/app')
	->register();

// Config.neon
$configurator->addConfig(__DIR__ . '/app/config.neon');
$configurator->onCompile[] = function($configurator, $compiler) {
	$compiler->addExtension('irc', new Nymph\Config\IrcExtension);
	$compiler->addExtension('nymph', new Nymph\Config\NymphExtension);
};
$container = $configurator->createContainer();

// Run it
$container->irc->bot->run();