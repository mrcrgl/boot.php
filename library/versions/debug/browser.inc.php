<?php

/**
 * Debug Handler "browser"
 *
 * @package     Versions.core
 * @subpackage  Base
 * @since       2.0
 */
class VDebugBrowser implements VDebugInterface {
	
	static $collection = array();
	
	static function init() {
		
	}
	
	static function report(VDebugMessage $message) {
		#var_dump($message);
		print $message;
	}
	
	static function get() {
		
	}
	
	
	static function get_last() {
		
	}
	
}