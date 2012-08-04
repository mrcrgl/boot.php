<?php


class VUrl {
	
	static $instance = null;
	
	var $pattern = array();
	
	var $_registred = array();
	
	public function __construct() {
		// register global urls?
		#printf("__construct() Class is %s".NL, get_class($this));
		if (get_class($this) == 'VUrl' && is_file(PROJECT_CONFIG.DS.'urls.inc.php')) {
			VLoader::register('ProjectUrls', PROJECT_CONFIG.DS.'urls.inc.php');
			$urls = new ProjectUrls();
			$this->register( $urls->getPattern() );
			
		}
		
		// loading first urls
		if (get_class($this) == 'ProjectUrls') {
			
		}
	}
	
	public static function getInstance() {
		if (!is_object(self::$instance)) {
			
			self::$instance = new VUrl();
		}

		return self::$instance;
	}
	
	public function parse($request_uri=null, $use_own_pattern=false) {
		
		VLoader::import('versions.utilities.array');
		
		#if ($use_own_pattern) {
		#	print_r("parse() class is %s".NL, get_class($this));
		#	return false;
		#}
		
		if (is_null($request_uri)) {
			$input =& VFactory::getInput();
			$request_uri   = $input->get('REQUEST_URI', '/', 'server');
		}
		
		$parsed_url = parse_url($request_uri);
		$path = implode( '/', VArray::strip_empty_values( explode('/', $parsed_url['path']), true ) );
		
		if (substr($path, -1) != '/') {
			$path = $path.'/';
		}
		#printf("Class is %s".NL, get_class($this));
		#$url =& VFactory::getUrl();
		#$patternlist = (($use_own_pattern) ? $this->getPattern() : $url->getPattern());
		$patternlist = $this->getPattern();
		
		foreach ($patternlist as $pattern => $destination) {
			#printf("Pattern: %s Required: %s".NL, htmlspecialchars($pattern), $path);
			$pattern = str_replace('/', '\/', $pattern);
			$epattern = '/'.$pattern.'/';
			
			if (preg_match($epattern, $path, $matches, PREG_OFFSET_CAPTURE)) {
				#print "<pre>";var_dump($matches);print "</pre><br />";
				
				foreach ($matches as $key => $match) {
					if (!is_numeric($key)) {
						$_GET[$key] = $match[0];
					}
 				}
				
				// found, next level
				if (substr($destination, 0, strlen('include:')) == 'include:') {
					$component_ident = substr($destination, strlen('include:'));
					
					#print "Have to include $component_ident".NL;
					
					$matched_part = $matches[0][0];
					#printf("Matched part: %s".NL, $matched_part);
					
					$remaining_part = str_replace($matched_part, '', $path);
					#printf("Continue with part: %s".NL, $remaining_part);
					
					$url_classname = sprintf("Component%sUrls", ucfirst($component_ident));
					$com_urls = new $url_classname();
					
					return $com_urls->parse($remaining_part, true);
					
				} else {
					
					list($com, $view, $method) = explode('.', $destination);
					
					$_GET['_vc'] = $com;
					$_GET['_vv'] = $view;
					$_GET['_vm'] = $method;
					#print_r($_GET);
					return true;
				}
				
			} else {
				// TODO
				#print "no match".NL;
			}
		}
		// TODO
		print "failed!";
		return false;
	}
	
	static public function _parse($url=null) {
		
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
	
	public function register($expression, $destination=null) {
		
		if (is_array($expression)) {
			foreach ($expression as $pattern => $dest) {
				$this->register($pattern, $dest);
			}
			return null;
		}
		
		$this->pattern[$expression] = $destination;
		#printf("Expression: %s Destination: %s".NL, $expression, $destination);
		
	}
	 
	
	function getPattern() {
		return $this->pattern;
	}
}