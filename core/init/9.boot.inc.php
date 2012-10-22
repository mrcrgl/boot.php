<?php
/**
 * Boot up the Framework.
 *
 * @license   MIT Licence (see LICENSE file)
 * @copyright 2012 Marc Riegel
 *
 * @author  Marc Riegel <mail@marclab.de>
 * @package boot.php.core
 * @subpackage Init
 */

// TODO: Check if this is used.
BLoader::import('boot.utilities.instance');

// Start routing.
$router =& BFactory::getRouter();
$router->route();
