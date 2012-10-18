<?php



require_once VLIB.DS.'versions'.DS.'base'.DS.'loader.inc.php';
VLoader::init();

VLoader::register('VProfiler', VLIB.DS.'versions'.DS.'debug'.DS.'profiler.inc.php');

/**
 * __autoload()
 * @param string $__classname
 * @return mixed path if class found, false if not
 */
function __autoload($__classname)
 {
    return VLoader::autoload($__classname);
}

spl_autoload_register();
spl_autoload_register('__autoload');
