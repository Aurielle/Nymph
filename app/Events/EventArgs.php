<?php

/**
 * This file part of Nymph.
 * 
 * Copyright (c) 2012 Vaclav Vrbka (aurielle@aurielle.cz)
 * 
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nymph\Events;

use Nymph, Nette;



/**
 * EventArgs is the base class for classes containing event data.
 *
 * This class contains no event data. It is used by events that do not pass state
 * information to an event handler when an event is raised. The single empty EventArgs
 * instance can be obtained through {@link getEmptyInstance}.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.0
 * @version $Revision: 3938 $
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 */
class EventArgs
{
    /**
     * @var EventArgs Single instance of EventArgs
     * @static
     */
    private static $_emptyEventArgsInstance;

    /**
     * Gets the single, empty and immutable EventArgs instance.
     *
     * This instance will be used when events are dispatched without any parameter,
     * like this: EventManager::dispatchEvent('eventname');
     *
     * The benefit from this is that only one empty instance is instantiated and shared
     * (otherwise there would be instances for every dispatched in the abovementioned form)
     *
     * @see EventManager::dispatchEvent
     * @link http://msdn.microsoft.com/en-us/library/system.eventargs.aspx
     * @static
     * @return EventArgs
     */
    public static function getEmptyInstance()
    {
        if ( ! self::$_emptyEventArgsInstance) {
            self::$_emptyEventArgsInstance = new EventArgs;
        }

        return self::$_emptyEventArgsInstance;
    }
}