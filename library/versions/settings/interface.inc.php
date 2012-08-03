<?php

interface VSettingsInterface {
	
	static function init();
	
	static function set($key, $value=false);
	
	static function get($key);
	
}