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
 * CompilerExtension
 */
class CompilerExtension extends Nette\Config\CompilerExtension
{
	/**
	 * @param string $alias
	 * @param string $service
	 */
	public function addAlias($alias, $service)
	{
		$this->getContainerBuilder()
			->addDefinition($alias)->setFactory('@' . $service);
	}
	
	
	/**
	 * Intersects the keys of defaults and given options and returns only not NULL values.
	 *
	 * @param array $given	   Configurations options
	 * @param array $defaults  Defaults
	 * @param bool $keepNull
	 *
	 * @return array
	 */
	public static function getOptions(array $given, array $defaults, $keepNull = FALSE)
	{
		$options = array_intersect_key($given, $defaults) + $defaults;

		if ($keepNull === TRUE) {
			return $options;
		}

		return array_filter($options, function ($value) {
			return $value !== NULL;
		});
	}
}