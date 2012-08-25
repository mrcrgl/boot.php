<?php

if (!defined('PROJECT_HTDOCS')) {
	echo 'constant PROJECT_HTDOCS not defined. Exiting...';
	exit(0);
}

/**
 * @var DS
 * @see DIRECTORY_SEPERATOR
 */
define('DS', DIRECTORY_SEPARATOR);

/**
 * @var NL Newline
 */
define('NL', ((PHP_SAPI != 'cli') ? '<br />' : "\n"));

/**
 * @var	VFRAMEWORK		Versions core directory
 */
define('VFRAMEWORK', realpath(dirname(__FILE__).DS.'..'.DS.'..'));
define('VROOT', realpath(VFRAMEWORK.DS.'..'));

/**
 * @var VCONFIG		Versions config directory
 */
define('VCONFIG', VFRAMEWORK.DS.'config');

/**
 * @var VLIB	Versions library
 */
define('VLIB', VFRAMEWORK.DS.'library');

/**
 * @var VCORE	Versions core
 */
define('VCORE', VFRAMEWORK.DS.'core');

/**
 * @var VCOMPONENTS	Versions components directory
 */
define('VCOMPONENTS', VFRAMEWORK.DS.'vcomponents');

/**
 * @var	VMODULES	Versions modules directory
 */
define('VMODULES', VFRAMEWORK.DS.'vmodules');

/**
 * @var	VMODELS	Versions modules directory
 */
define('VMODELS', VFRAMEWORK.DS.'vmodels');

/**
 * @var	VPLUGINS	Versions plugin directory
 */
define('VPLUGINS', VFRAMEWORK.DS.'vplugins');

/**
 * @var	VMIDDLEWARES	Versions middleware directory
 */
define('VMIDDLEWARES', VFRAMEWORK.DS.'vmiddlewares');

/**
 * @var	VTEMPLATES	Versions template directory
 */
define('VTEMPLATES', VFRAMEWORK.DS.'templates');

/**
 * @var PROJECT_ROOT		Project root directory
 */
define('PROJECT_ROOT', realpath(PROJECT_HTDOCS.DS.'..'));

/**
 * @var PROJECT_CONFIG		Project config directory
 */
define('PROJECT_CONFIG', PROJECT_ROOT.DS.'config');

/**
 * @var PROJECT_COMPONENTS		Project config directory
 */
define('PROJECT_COMPONENTS', PROJECT_ROOT.DS.'components');

/**
 * @var PROJECT_MODELS		Project models directory
 */
define('PROJECT_MODELS', PROJECT_ROOT.DS.'models');

/**
 * @var PROJECT_PLUGINS		Project config directory
 */
define('PROJECT_PLUGINS', PROJECT_ROOT.DS.'plugins');

/**
 * @var	PROJECT_MIDDLEWARES	Project middleware directory
 */
define('PROJECT_MIDDLEWARES', PROJECT_ROOT.DS.'middlewares');

/**
 * @var PROJECT_CACHE Project cache directory
 */
define('PROJECT_CACHE', PROJECT_ROOT.DS.'cache');

/**
 * @var PROJECT_TEMPLATES Project template directory
 */
define('PROJECT_TEMPLATES', PROJECT_ROOT.DS.'templates');


/**
 * @var 'HTTP_USER' User which runs the Webserver
 */
$http_user = posix_getpwuid(posix_getuid()); // Get http user
define('HTTP_USER', $http_user["name"]);

// Check permissions for cache directory
if (!is_writable(PROJECT_CACHE)){
  echo PROJECT_CACHE.' must be writeable by '.HTTP_USER.'. Exiting...';
  exit(0);
}
