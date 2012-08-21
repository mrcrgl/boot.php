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
	
	public function getDestinationComponent($destination) {
		if (substr($destination, 0, strlen('include:')) == 'include:') {
			return substr($destination, strlen('include:'));
		}
		return false;
	}
	
	public function splitDestination($destination, $publish_args=true) {
		if (is_array($destination)) {
 			$temp = $destination;
 			$args = array();
 			if (isset($temp[1])) $args = $temp[1];
 			$destination = $temp[0];
 			if ($publish_args) {
	 			foreach ($args as $argk => $argv) {
	 				$_GET[$argk] = $argv;
	 			}
 			}
 		}
 		return $destination;
	}
	
	public function parse($request_uri=null, $chained_uri=null) {
		
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
		
		/*if (substr($path, 0, 1) != '/') {
			$path = '/'.$path;
		}*/
		
		if (!$chained_uri) $chained_uri = $path;
		
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
 				
 				$destination = $this->splitDestination($destination);
 				
 				/*
 				 * register template path to renderer
 				 */
				$document =& VFactory::getDocument();
				$renderer =& $document->getRenderer();
				$object = new ReflectionObject($this);
				$template_path = dirname($object->getFileName()).DS.'templates';
				#print "<pre>";
					
				#print $template_path.NL;
				$renderer->addTemplateDir($template_path);
 				#var_dump($renderer->getTemplateDir());
 				#print "</pre>";
 				
				// found, next level
				if ($component_ident = $this->getDestinationComponent($destination)) {	
					#print "Have to include $component_ident".NL;
					
					$matched_part = $matches[0][0];
					#printf("Matched part: %s".NL, $matched_part);
					
					$remaining_part = str_replace($matched_part, '', $path);
					#printf("Continue with part: %s".NL, $remaining_part);
					
					$url_classname = sprintf("Component%sUrls", ucfirst($component_ident));
					$com_urls = new $url_classname();
					
					if (substr($remaining_part, -1) != '/') {
						$remaining_part = $remaining_part.'/';
					}
					
					/*
					 * Url prefix to able the component building urls
					 */
					#print "Chained: ".$chained_uri.NL;
					#print "Remaining: ".$remaining_part.NL;
					#print(str_replace($remaining_part, '', $chained_uri));
					
					#print substr($chained_uri, '-'.strlen($remaining_part));
					#printf(NL."Chained Uri: %s contains %s (length %d)".NL, $chained_uri, $remaining_part, strlen($remaining_part));
					if (substr($chained_uri, '-'.strlen($remaining_part)) == $remaining_part) {
						#print "TRUE substr($chained_uri, 0, strlen($remaining_part)) == $remaining_part".NL;
						$document->setUrlPrefix( substr($chained_uri, 0, strlen($chained_uri)-(strlen($remaining_part))) );
					} else {
						$document->setUrlPrefix( $chained_uri );
					}
					#print "URL Prefix: ".$document->getUrlPrefix().NL;
					#print NL;
					#$document->setUrlPrefix( str_replace($remaining_part, '', $chained_uri) );
					
					return $com_urls->parse($remaining_part, $chained_uri);
					
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