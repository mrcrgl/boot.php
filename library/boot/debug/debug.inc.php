<?php

BLoader::import('boot.debug.interface');
/**
 *
 *
 * @package     boot.php.core
 * @subpackage  Base
 * @since       2.0
 */
class BDebug implements BDebugInterface
{

    static $handler;

    static function init()
   {
        $handler = BSettings::f('debug.handler', 'none');
        self::$handler = __CLASS__.ucfirst($handler);

        if (!class_exists(self::$handler)) {
            throw new Exception("BDebug Handler ".$handler." not found!");
        }

        // TODO: Perfomante lösung finden
        #set_error_handler('BDebug::php_error_handler');
        #set_exception_handler('BDebug::php_exception_handler');

        return call_user_func(self::$handler.'::init');
    }

    static function _(BDebugMessage $message)
    {
        return self::report($message);
    }

    static function report(BDebugMessage $message)
    {

      if (!$message->valid) return false;

      /*if (BSettings::f('default.debug')) {
        if (!class_exists('BProfiler'))
{
          BLoader::import('boot.debug.profiler');
        }
        $profiler = BProfiler::getInstance('debug');
        $profiler->mark($message);
      }*/

      if (BSettings::f('default.debug', false) != 1) {
        return false;
      }

        if (self::$handler)
            return call_user_func(self::$handler.'::report', $message);
    }

    static function get()
    {
        if (self::$handler)
            return call_user_func(self::$handler.'::get');
    }

    static function get_last()
    {
        if (self::$handler)
            return call_user_func(self::$handler.'::get_last');
    }

    /**
     *
     * PHP correspondenting error handler
     * @param integer $errno
     * @param string  $errstr
     * @param string  $errfile
     * @param integer $errline
     * @param array   $errcontext
     */
    static function php_error_handler($errno, $errstr, $errfile=null, $errline=null, $errcontext=null)
     {
        self::report(new BDebugMessage($errstr, null, $errno, $errfile, $errline));
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $exception
     */
    static function php_exception_handler($exception)
     {
        self::report(new BDebugMessage($exception->getMessage(), DEBUG_EXCEPTION, null, $exception->getFile(), $exception->getLine()));
    }
}
