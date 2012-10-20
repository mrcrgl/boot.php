<?php
/**
 * Boot up the Framework.
 *
 * @license   MIT Licence (see LICENSE file)
 * @copyright 2012 Marc Riegel
 *
 * @author  Marc Riegel <mail@marclab.de>
 * @package Versions.core
 * @subpackage Init
 */

// TODO: Check if this is used.
VLoader::import('versions.utilities.instance');

// Start routing.
$router =& VFactory::getRouter();
$router->route();
