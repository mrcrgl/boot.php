<?php
/**
 * Process global autoloader.
 *
 * @license   MIT Licence (see LICENSE file)
 * @copyright 2012 Marc Riegel
 *
 * @author  Marc Riegel <mail@marclab.de>
 * @package Versions.core
 * @subpackage Init
 */

require_once VLIB.DS.'versions'.DS.'base'.DS.'loader.inc.php';

VLoader::init();

VLoader::register(
    'VProfiler', VLIB.DS.'versions'.DS.'debug'.DS.'profiler.inc.php'
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
    return VLoader::autoload($sClassname);
}

spl_autoload_register();
spl_autoload_register('__autoload');
