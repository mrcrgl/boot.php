<?php


abstract class VUrl {
	
	static public function parse($url=null) {
		
		VLoader::import('versions.utilities.array');
		
		if (!$url) {
			$input =& VFactory::getInput();
			$url   = $input->get('REQUEST_URI', 'index', 'server');
		}
		
		$parsed_url = parse_url($url);
		$path_parts = VArray::strip_empty_values( explode('/', $parsed_url['path']), true );
		
		$_GET['_vc'] = VArray::get($path_parts, 0);
		$_GET['_vv'] = VArray::get($path_parts, 1);
		$_GET['_vm'] = VArray::get($path_parts, 2);
		
	}
}