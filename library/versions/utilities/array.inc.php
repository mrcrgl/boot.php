<?php

abstract class VArray {
	
	static public function strip_empty_values($array, $reorder_keys=false) {
		VLoader::import('versions.utilities.validator');
		
		if (!is_array($array)) {
			return array();
		}
		
		foreach ($array as $key => $value) {
			if (!Validator::is($value, 'filled')) {
				unset($array[$key]);
			}
		}
		
		if ($reorder_keys) {
			$temp = $array;
			$array = array();
			
			foreach ($temp as $value) {
				$array[] = $value;
			}
		}
		
		return $array;
	}
	
	static public function get($array, $key=0, $default=null) {
		if (!is_array($array)) return null;
		return ((array_key_exists($key, $array)) ? $array[$key] : $default);
	}
	
}