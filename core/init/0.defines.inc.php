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


// Define: @var BFRAMEWORK Versions core directory .
define('BFRAMEWORK', realpath(dirname(__FILE__).DS.'..'.DS.'..'));
define('BROOT', realpath(BFRAMEWORK.DS.'..'));


// Define: @var BCONFIG Versions config directory .
define('BCONFIG', BFRAMEWORK.DS.'config');


// Define: @var BLIB    boot library .
define('BLIB', BFRAMEWORK.DS.'library');


// Define: @var BCORE    Versions core .
define('BCORE', BFRAMEWORK.DS.'core');


// Define: @var BCOMPONENTS    Versions components directory .
define('BCOMPONENTS', BFRAMEWORK.DS.'bcomponents');


// Define: @var BMODULES    Versions modules directory .
define('BMODULES', BFRAMEWORK.DS.'bmodules');


// Define: @var BMODELS    Versions modules directory .
define('BMODELS', BFRAMEWORK.DS.'bmodels');


// Define: @var BPLUGINS    Versions plugin directory .
define('BPLUGINS', BFRAMEWORK.DS.'bplugins');


// Define: @var BMIDDLEWARES    Versions middleware directory .
define('BMIDDLEWARES', BFRAMEWORK.DS.'bmiddlewares');


// Define: @var BTEMPLATES    Versions template directory .
define('BTEMPLATES', BFRAMEWORK.DS.'templates');


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
