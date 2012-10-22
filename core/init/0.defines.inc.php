<?php
/**
 * Process global defines.
 *
 * @license   MIT Licence (see LICENSE file)
 * @copyright 2012 Marc Riegel
 *
 * @author  Marc Riegel <mail@marclab.de>
 * @package boot.php.core
 * @subpackage Init
 */

if (!defined('PROJECT_HTDOCS')) {
    echo 'constant PROJECT_HTDOCS not defined. Exiting...';
    exit(0);
}


// Define: @var DS | @see DIRECTORY_SEPERATOR .
define('DS', DIRECTORY_SEPARATOR);


// Define: @var NL Newline .
define('NL', ((PHP_SAPI != 'cli') ? '<br />' : "\n"));


// Define: @var VFRAMEWORK Versions core directory .
define('VFRAMEWORK', realpath(dirname(__FILE__).DS.'..'.DS.'..'));
define('VROOT', realpath(VFRAMEWORK.DS.'..'));


// Define: @var VCONFIG Versions config directory .
define('VCONFIG', VFRAMEWORK.DS.'config');


// Define: @var VLIB    boot library .
define('VLIB', VFRAMEWORK.DS.'library');


// Define: @var VCORE    Versions core .
define('VCORE', VFRAMEWORK.DS.'core');


// Define: @var VCOMPONENTS    Versions components directory .
define('VCOMPONENTS', VFRAMEWORK.DS.'vcomponents');


// Define: @var VMODULES    Versions modules directory .
define('VMODULES', VFRAMEWORK.DS.'vmodules');


// Define: @var VMODELS    Versions modules directory .
define('VMODELS', VFRAMEWORK.DS.'vmodels');


// Define: @var VPLUGINS    Versions plugin directory .
define('VPLUGINS', VFRAMEWORK.DS.'vplugins');


// Define: @var VMIDDLEWARES    Versions middleware directory .
define('VMIDDLEWARES', VFRAMEWORK.DS.'vmiddlewares');


// Define: @var VTEMPLATES    Versions template directory .
define('VTEMPLATES', VFRAMEWORK.DS.'templates');


// Define: @var PROJECT_ROOT        Project root directory .
define('PROJECT_ROOT', realpath(PROJECT_HTDOCS.DS.'..'));


// Define: @var PROJECT_CONFIG        Project config directory .
define('PROJECT_CONFIG', PROJECT_ROOT.DS.'config');


// Define: @var PROJECT_COMPONENTS        Project config directory .
define('PROJECT_COMPONENTS', PROJECT_ROOT.DS.'components');


// Define: @var PROJECT_MODELS        Project models directory .
define('PROJECT_MODELS', PROJECT_ROOT.DS.'models');


// Define: @var PROJECT_PLUGINS        Project config directory .
define('PROJECT_PLUGINS', PROJECT_ROOT.DS.'plugins');


// Define: @var PROJECT_MIDDLEWARES    Project middleware directory .
define('PROJECT_MIDDLEWARES', PROJECT_ROOT.DS.'middlewares');


// Define: @var PROJECT_CACHE Project cache directory .
define('PROJECT_CACHE', PROJECT_ROOT.DS.'cache');


// Define: @var PROJECT_TEMPLATES Project template directory .
define('PROJECT_TEMPLATES', PROJECT_ROOT.DS.'templates');


// Define: @var 'HTTP_USER' User which runs the Webserver .
if (function_exists('posix_getuid')) {
    // Get http user.
    $sHttpUser = posix_getpwuid(posix_getuid());
    define('HTTP_USER', $sHttpUser["name"]);
} else {
    define('HTTP_USER', ":undef:");
}


// Check permissions for cache directory.
if (!is_writable(PROJECT_CACHE)) {
  echo PROJECT_CACHE.' must be writeable by '.HTTP_USER.'. Exiting...';
  exit(0);
}
