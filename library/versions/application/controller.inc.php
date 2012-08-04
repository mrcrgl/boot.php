<?php

VLoader::import('versions.base.object');

class VApplicationController extends VObject {

	static private $components = null;
	
	var $default_view 			= null;
	
	var $component_root 		= null;
	
	var $component_name 		= null;
	
	var $component_settings = null;
	
	var $request_view_classname = null;
	
	var $request_view_method = null;
	
	public function __construct() {
		
	}
	
	static public function getInstance($component=null) {
		
		if (!$component) {
			$input =& VFactory::getInput();
			$component = $input->get('_vc', 'default', 'get');
		}
		
		$controller = self::getControllerByPrefix($component);
		
		if (!Validator::is($controller, 'filled')) {
			throw new Exception( sprintf("Controller for component '%s' not found or component does not exist!", $component) );
		}
		
		VLoader::autoload($controller);
		
		if (!class_exists($controller)) {
			throw new Exception( sprintf("Controller '%s' not found!", $controller) );
		}
		
		VLoader::import('versions.utilities.array');

		$ref = new $controller();
		$ref->set('component_root', dirname( VArray::get(VLoader::$registred, $controller) ));
		$ref->set('component_name', $component);
		
		$config = new VSettingsIni();
		$config->init($ref->get('component_root'), 'controller');
		
		$ref->set('component_settings', $config);
		
		return $ref;
	}
	
	static public function getControllerByPrefix($prefix) {
		if (!self::$components) {
			self::scanComponents();
		}
		
		VLoader::import('versions.utilities.array');
		
		$path = VArray::get(self::$components, $prefix);
		
		return $path;
	}
	
	static public function scanComponents() {
		self::$components = array();
		
		foreach (array(PROJECT_COMPONENTS, VCOMPONENTS) as $path) {
			
			if (is_dir($path)) {
			
				foreach (scandir($path) as $component_dir) {
					if ($component_dir == '.' || $component_dir == '..') continue;
					if (!is_dir($path.DS.$component_dir)) continue;
					
					if (!is_file($path.DS.$component_dir.DS.'controller.ini')) continue;
					
					if (!is_file($path.DS.$component_dir.DS.'urls.inc.php')) continue;

					
					$config = new VSettingsIni();
					$config->init($path.DS.$component_dir, 'controller');
					
					$alias 	= $config->get('controller.alias', $component_dir);
					$file 	= $path.DS.$component_dir.DS.$config->get('controller.file', 'controller.inc.php');
					
					$classname = sprintf('Component%sController', ucfirst($component_dir));
					#$url_classname = 'ComponentUrls'.ucfirst($component_dir);
					
					/* get urls */
					/*VLoader::register($url_classname, $path.DS.$component_dir.DS.'urls.inc.php');*/
					/*$urls = new $url_classname();*/
					
					/*$url =& VFactory::getUrl();
					$url->register( $urls->getPattern() );*/
					
					#VLoader::register($classname, $file);
					
					self::$components[$alias] = $classname;					
				}
				
			}
		}
	}
	
	public function handleRequest() {
		
		VMiddleware::trigger('onBeforePrepareRequest');
		$this->prepareRequest();
		VMiddleware::trigger('onAfterPrepareRequest');
		
		VMiddleware::trigger('onBeforeProcessRequest');
		$this->processRequest();
		VMiddleware::trigger('onAfterProcessRequest');
		
		/* switch to response */
		VMiddleware::trigger('onBeforePrepareResponse');
		$this->prepareResponse();
		VMiddleware::trigger('onAfterPrepareResponse');
		
		VMiddleware::trigger('onBeforePrintResponse');
		$this->printResponse();
		VMiddleware::trigger('onAfterPrintResponse');
		
		VMiddleware::trigger('onBeforeQuit');
		$this->quit();
		
	}
	
	public function prepareRequest() {
		$view_ident = $this->getRequestView();
		
		// Import view file
		if (!VLoader::check_extensions($this->component_root.DS.'views'.DS.$view_ident))
			throw new Exception( sprintf("View not fount at: %s", $this->component_root.DS.'views'.DS.$view_ident) );
		

		
		$view = $this->getViewClassname( $view_ident );
		
		$method = $this->getRequestMethod();
		
		$this->set('request_view_classname', $view);
		$this->set('request_view_method', $method);
		
	}
	
	public function processRequest() {
		
		$view_class = $this->get('request_view_classname');
		$method			= $this->get('request_view_method');
		
		
		$view = new $view_class();
		
		if (!method_exists($view, $method))
			throw new Exception( sprintf("Method '%s' not registred in view '%s'", $method, $view_class) );
		
		
		// prepare
		VMiddleware::trigger('onBeforePrepareView');
		$view->prepare();
		VMiddleware::trigger('onAfterPrepareView');
		
		// process method
		VMiddleware::trigger('onBeforeProcessView');
		$view->$method();
		VMiddleware::trigger('onAfterProcessView');
		
		// cleanup
		VMiddleware::trigger('onBeforeCleanupView');
		$view->cleanup();
		VMiddleware::trigger('onAfterCleanupView');
		
		
	}
	
	public function prepareResponse() {
		
		$document =& VFactory::getDocument();
		$document->render();
		
	}
	
	public function printResponse() {
		
		$document =& VFactory::getDocument();
		print $document->getBody();
		
	}
	
	public function getRequestView() {
		
		$input =& VFactory::getInput();
		$view_ident = $input->get('_vv', $this->default_view, 'get');
		
		/*
		 * TODO: 404 weiterleitung? macht sinn wegen dupplicate content...
		 */
		if (!$view_ident) $view_ident = $this->default_view;
		
		return $view_ident;
	}
	
	public function getRequestMethod() {
		
		$input =& VFactory::getInput();
		$method = $input->get('_vm', 'show', 'get');
		
		/*
		 * TODO: 404 weiterleitung? macht sinn wegen dupplicate content...
		 */
		if (!$method) $method = 'show';
		
		return $method;
	}
	
	public function getViewClassname($view_ident) {
		$classname = sprintf('Component%sView%s', ucfirst($this->get('component_name')), ucfirst($view_ident));
		
		return $classname;
	}
	
	public function quit() {
		exit(0);
	}
}