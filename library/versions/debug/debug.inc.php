<?php


/**
 * 
 *
 * @package     Versions.core
 * @subpackage  Base
 * @since       2.0
 */
class VDebug implements VDebugInterface {
	
	static $handler;
	
	static function init() {
		$handler = VSettings::f('debug.handler', 'none');
		self::$handler = __CLASS__.ucfirst($handler);
		
		if (!class_exists(self::$handler)) {
			throw new Exception("VDebug Handler ".$handler." not found!");
		}
		
		#set_error_handler('VDebug::php_error_handler');
		#set_exception_handler('VDebug::php_exception_handler');
		
		return call_user_func(self::$handler.'::init');
	}
	
	static function _(VDebugMessage $message) {
		return self::report($message);
	}
	
	static function report(VDebugMessage $message) {
		if (self::$handler)
			return call_user_func(self::$handler.'::report', $message);
	}
	
	static function get() {
		if (self::$handler)
			return call_user_func(self::$handler.'::get');
	}
	
	static function get_last() {
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
	static function php_error_handler($errno, $errstr, $errfile=null, $errline=null, $errcontext=null) {
		self::report(new VDebugMessage($errstr, null, $errno, $errfile, $errline));
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $exception
	 */
	static function php_exception_handler($exception) {
		self::report(new VDebugMessage($exception->getMessage(), DEBUG_EXCEPTION, null, $exception->getFile(), $exception->getLine()));
	}
}