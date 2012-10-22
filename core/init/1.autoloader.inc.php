<?php
/**
 * Process global autoloader.
 *
 * @license   MIT Licence (see LICENSE file)
 * @copyright 2012 Marc Riegel
 *
 * @author  Marc Riegel <mail@marclab.de>
 * @package boot.php.core
 * @subpackage Init
 */

require_once VLIB.DS.'boot'.DS.'base'.DS.'loader.inc.php';

BLoader::init();

BLoader::register(
    'BProfiler', VLIB.DS.'boot'.DS.'debug'.DS.'profiler.inc.php'
);

BLoader::register(
    'BObject', VLIB.DS.'boot'.DS.'base'.DS.'object.inc.php'
);

/**
 * Autoloader registration.
 *
 * @param string $sClassname The class to load.
 *
 * @return mixed path if class found, false if not
 */
function __autoload($sClassname)
{
    return BLoader::autoload($sClassname);
}

spl_autoload_register();
spl_autoload_register('__autoload');
