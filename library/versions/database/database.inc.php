<?php

VLoader::discover(dirname(__FILE__).DS.'adapter');

abstract class VDatabase {
	
	static public function getInstance($type='mysql') {
		
		$classname = 'VDatabase'.ucfirst($type);
		
		if (!class_exists($classname)) {
			throw new Exception( sprintf('Database adapter %s not found. Exiting...', $classname) );
			//user_error()
		}
		
		return new $classname();
	}
	
}