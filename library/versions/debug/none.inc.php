<?php

/**
 * Debug Handler "none"
 *
 * @package     Versions.core
 * @subpackage  Debug
 * @since       2.0
 */
class VDebugNone implements VDebugInterface {
	
	static $collection = array();
	
	static function init() {
		
	}
	
	static function report(VDebugMessage $message) {
		array_push(self::$collection, $message);
	}
	
	static function get() {
		
	}
	
	static function get_last() {
		
	}
	
}