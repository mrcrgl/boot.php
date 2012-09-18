<?php

/**
 * VSettings
 *
 * @package     Versions.core
 * @subpackage  Settings
 * @since       2.0
 */
class VSettings implements VSettingsInterface {

  /**
   *
   * @var bool
   */
  static $initialized = false;

	/**
	 * Settings-Handler class
	 * @var string
	 */
	static $handler = 'VSettingsIni';

	/**
	 *
	 * Enter description here ...
	 */
	static function init() {
	  self::$initialized = true;
		return call_user_func(self::$handler.'::init');
	}

	/**
	 *
	 * Enter description here ...
	 * @param string $key
	 * @param string $default
	 */
	static function f($key, $default=null) {
		return self::get($key, $default);
	}

	/**
	 *
	 * Enter description here ...
	 * @param string $key
	 * @param string $default
	 */
	static function get($key, $default=null) {
		$return = call_user_func(self::$handler.'::get', $key);
		if (!$return) {
			$return = $default;
		}

		return $return;
	}

	/**
	 *
	 * Enter description here ...
	 * @param string $key
	 * @param string $value
	 */
	static function set($key, $value=false) {
		return call_user_func(self::$handler.'::set', $key, $value);
	}

}