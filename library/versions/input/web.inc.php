<?php


class VInputWeb extends VInput {
	
	public function __construct() {
		$url =& VFactory::getUrl();
		$url->parse($this->get('REQUEST_URI', 'index', 'server'));
	}
	
	
	public function get($attribute, $default=null, $method=null) {
		
		if (is_null($method)) {
			$data =& $_REQUEST;
		} elseif ('get' == VString::strtolower($method)) {
			$data =& $_GET;
		} elseif ('post' == VString::strtolower($method)) {
			$data =& $_POST;
		} elseif ('cookie' == VString::strtolower($method)) {
			$data =& $_COOKIE;
		} elseif ('server' == VString::strtolower($method)) {
			$data =& $_SERVER;
		}
	
		if (array_key_exists($attribute, $data)) {
			return $data[$attribute];
		}
		
		return $default;
	}
}