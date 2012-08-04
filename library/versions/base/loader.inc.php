<?php


require_once VLIB.DS.'versions'.DS.'string.inc.php';
require_once VLIB.DS.'versions'.DS.'debug'.DS.'debug.inc.php';

/**
 * Common object loader
 *
 * @package     Versions.core
 * @subpackage  Base
 * @since       2.0
 */
class VLoader {
	
	static $extensions = array('.inc.php', '.class.php', '.php');
	
	/**
	 * Array of manually registred classes
	 * @var array
	 */
	static $registred = array();
	
	/**
	 * Array of imported classes
	 * @var array
	 */
	static $imported = array();
	
	/**
	 * The init() class
	 * 
	 * @return void
	 */
	static function init() {
		
	}
	
	/**
	 * 
	 * The file() class
	 * 
	 * @param string $__file
	 */
	static function file($__file) {
		if (is_file($__file) && !in_array($__file, self::$imported)) {
			require_once $__file;
			array_push(self::$imported, $__file);
			VDebug::_(new VDebugMessage("Loaded file: ".$__file, DEBUG_MESSSAGE));
			return true;
		} else {
			VDebug::_(new VDebugMessage("File not found: ".$__file, DEBUG_ERROR));
			return false;
		}
	}
	
	/**
	 * 
	 * The import() class 
	 * 
	 * @see 	get_class_path
	 * @param string $__dotted_identifier
	 */
	static function import($__dotted_identifier) {
		return self::get_class_path($__dotted_identifier);
	}
	
	
	/**
	 * 
	 * Scans a directory for files and include them
	 * 
	 * @param string $__path
	 * 
	 * @param boolean $recursive
	 */
	static function discover($__path, $recursive = false) {
		if (is_dir($__path)) {
			
			VDebug::report(new VDebugMessage("Discover directory: ".$__path), DEBUG_NOTICE, 1);
			
			foreach (scandir($__path) as $file) {
				
				/*
				 * skip . and ..
				 */
				if ($file == '.' || $file == '..') continue;
				
				/*
				 * if file is a directory and recursive is true, walk trough it
				 */  
				if (is_dir($file) && $recursive == true) {
					self::discover($__path.DS.$file, $recursive);
				}
				
				/*
				 * look for a valid extension and include the file 
				 */
				else {
					foreach (self::$extensions as $ext) {
						if (strpos($file, $ext) !== false) {
							self::file($__path.DS.$file);
						}
					}
				}
				
			}
		}
		else {
			VDebug::report(new VDebugMessage("Directory does not exist: ".$__path), DEBUG_NOTICE, 1);
		}
	}
	
	/**
	 * 
	 * Autoload layer for PHP's magic function __autoload()
	 * @param string $__classname
	 * 
	 * @return string Path
	 */
	static function autoload($__classname) {
		#print $__classname.NL;
		$path = self::get_class_path($__classname);
		if (!$path) {
			VDebug::report(new VDebugMessage("Class '".$__classname."' not found! Could not find file."), DEBUG_ERROR);
		} 
		elseif (!class_exists($__classname)) {
			VDebug::report(new VDebugMessage("Class '".$__classname."' not found! File found at '". $path ."' but class does not exist."), DEBUG_ERROR);
		}
		
		return $path;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param string $__classname
	 */
	static function get_class_path($__classname) {
		// check if class is registered
		if (isset(self::$registred[$__classname])) {
			#print $__classname." is registred as ".self::$registred[$__classname].NL;
			return self::file(self::$registred[$__classname]);
		}
		#print "$__classname";
		if (substr($__classname, 0, 1) == "V") {
			// it's a V-Class
			$__classname = 'Versions'.VString::substr($__classname, 1);
			$path = VString::strtolower(implode(DS, VString::splitCamelCase($__classname)));
			$classpath = self::check_extensions(VLIB.DS.$path);
			if ($classpath === false) {
				$parts = explode(DS, $path);
				$last = $parts[(count($parts)-1)];
				$parts[] = $last;
				$classpath = self::check_extensions(VLIB.DS.implode(DS, $parts));
			}
			
			return $classpath;
		}
		
		/*
		 * components
		 */
		elseif (substr($__classname, 0, 9) == "Component") {
			if (strpos($__classname, 'Model')) {
				$eparts = VString::splitCamelCase($__classname);
				foreach ($eparts as $i => $epart) {
					if ($epart == 'Model') {
						$eparts[$i] = 'Models';
					}
				}
				$__classname = implode('', $eparts);
			}
				
			$path = VString::strtolower(implode(DS, VString::splitCamelCase($__classname))); // ComponentNewsModelNews
			$path = str_replace('component/', '', $path);
			foreach (array(PROJECT_COMPONENTS, VCOMPONENTS) as $component_path) {
				#print $component_path.DS.$path.NL;
				$classpath = self::check_extensions($component_path.DS.$path);
				if ($classpath !== false) {
					self::register($__classname, $classpath);
					return $classpath;
				}
			}
			
		}
		/*
		 * trying to get the path
		 */
		elseif (strpos($__classname, '.') !== false) {
			$parts = explode('.', $__classname);
			
			if (is_dir(VLIB.DS.$parts[0])) {
				$path = self::check_extensions( VLIB.DS.implode(DS, $parts) );
				if ($path === false) {
					$last = $parts[(count($parts)-1)];
					$parts[] = $last;
					$path = self::check_extensions( VLIB.DS.implode(DS, $parts) );
				}
				return $path;
			}
			
		}
		// Models
		else {
			foreach (array(PROJECT_MODELS, VMODELS) as $paths) {
				#print "sdfgsdfgd";
				
				$path = VString::strtolower(implode(DS, VString::splitCamelCase($__classname)));
				
				
				$classpath = self::check_extensions($paths.DS.$path);
				if ($classpath === false) {
					$parts = explode(DS, $path);
					$last = $parts[(count($parts)-1)];
					$parts[] = $last;
					$classpath = self::check_extensions($paths.DS.implode(DS, $parts));
				}
					
				return $classpath;
			}
				
			throw new Exception("Unknown class_path layout: ".$__classname);
		}
		
		return false;
	}
	
	/**
	 * 
	 * The check_extension() class
	 * @param string $__part
	 */
	static function check_extensions($__part, $include=true) {
		foreach (self::$extensions as $ext) {
			if (is_file($__part.$ext)) {
				if ($include) self::file($__part.$ext);
				return $__part.$ext;
			}
		}
		return false;
	}
	
	/**
	 * 
	 * Registers a class with a correspondenting file
	 * 
	 * @param string $__classname
	 * @param string $__path
	 * @return void
	 */
	static function register($__classname, $__path) {
		if (!isset(self::$registred[$__classname])) {
			self::$registred[$__classname] = $__path;
		}
	}
	
	/**
	 * 
	 * The load_objects() class
	 */
	static function load_objects() {
		include_once 'fileutils'.DS.'IniFile.class.php';
	}
}

