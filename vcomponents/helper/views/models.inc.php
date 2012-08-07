<?php

class ComponentHelperViewModels extends VApplicationView {
	
	public function show() {
		
		$document =& VFactory::getDocument();
		$document->setTemplate('database/index.htpl');
		$document->assign('_current_step_tpl', 'models/step/show.htpl');
		
		VLoader::register('ProjectUrls', PROJECT_CONFIG.DS.'urls.inc.php');
		$project_urls = new ProjectUrls();
		
		$return = $this->parsePattern($project_urls, 'project', true);
		
		$document->assign('map', $return);
		
	}
	
	private function parsePattern($urls, $name='project', $is_root=false) {
		$return = array();
		
		$return['name'] = $name; 
		
		$ro = new ReflectionObject($urls);
		$models_path = (($is_root) ? realpath(dirname($ro->getFileName()).DS.'..'.DS.'models') : dirname($ro->getFileName()).DS.'models');
		$component_root_path = (($is_root) ? null : dirname($ro->getFileName()));
		#printf("Models coud be here: %s".NL, $models_path);
		
		$return['vmodels'] = $this->getRequiredModels($component_root_path);
		$return['models'] = $this->scanModels($models_path);
		
		
		foreach ($urls->getPattern() as $destination) {
			
			$destination = $urls->splitDestination($destination, false);
			
			if (!$component_ident = $urls->getDestinationComponent($destination)) {
				#print $destination.' is not a component.'.NL;
				continue;
			}
			
			#print "Component ".$component_ident.NL;
			
			$url_classname = sprintf("Component%sUrls", ucfirst($component_ident));
			$surls = new $url_classname();
			
			$return['components'] = $this->parsePattern($surls, $component_ident);
		}
		return $return;
	}
	
	private function getRequiredModels($config_path) {
		if (!$config_path) return array();
		if (!is_file($config_path.DS.'controller.ini')) return array();
		
		$config = parse_ini_file($config_path.DS.'controller.ini', true);
		
		if (!isset($config['model']) || !isset($config['model']['require'])) return array();
		
		return $config['model']['require'];
	}
	
	private function scanModels($models_path) {
		if (!is_dir($models_path)) return null;
		
		$models = array();
		
		$handle = opendir($models_path);

		if (!$handle) die( sprintf('Cannot open directory \'%s\''.NL, $models_path) );
		
		while (false !== ($file = readdir($handle))) {
			if (is_dir($models_path.DS.$file) && $file != '.' && $file != '..') {
				$models = array_merge($models, $this->scanModels($models_path.DS.$file));
				continue;
			}
			
			if (!preg_match('!\.php$!', $file)) {
				continue;
			}
			
			$models = $this->getClassesOfFile($models_path.DS.$file, $models);
		}
		
		return $models;
	}
	
	private function getClassesOfFile($file, $classes=array()) {
		$php_file = file_get_contents($file);
		$tokens = token_get_all($php_file);
		$class_token = false;
		foreach ($tokens as $token) {
		  if (is_array($token)) {
		    if ($token[0] == T_CLASS) {
		       $class_token = true;
		    } else if ($class_token && $token[0] == T_STRING) {
		    	VLoader::register($token[1], $file);
		      $temp = new $token[1]();
		      if (isset($temp->_DataMap)) {
		      	$classes[] = $token[1];
		      } 
		    	$class_token = false;
		    }
		  }       
		}
		return $classes;
	}
}