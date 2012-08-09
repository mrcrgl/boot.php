<?php

VLoader::register('Smarty', VLIB.DS.'Smarty'.DS.'Smarty.class.php');


class VDocumentRendererHtmlSmarty extends Smarty {
	
	public function __construct() {
		parent::__construct();
		
		
		
	}
	
	public function init() {
		/*
		 * collecting template directories
		 * 1. project component template if available
		 * 2. project template if available
		 * 3. framework component template if available
		 * 4. framework template if available
		 */
		$controller 		=& VFactory::getController();
		$component 			= $controller->component_name;
		$component_root = $controller->component_root;
		$template_dirs 	= array();
		
		/* MR: removed unnštig? */
		/*$project_component_tpl_dir = PROJECT_TEMPLATES.DS.'components'.DS.$component;
		if (is_dir($project_component_tpl_dir))
			$template_dirs[] = $project_component_tpl_dir;*/
		
		#if (is_dir($component_root.DS.'templates'))
		#	$template_dirs[] = $component_root.DS.'templates';
		
		if (is_dir(PROJECT_TEMPLATES))
			$template_dirs[] = PROJECT_TEMPLATES;
		
		
		if (is_dir(VTEMPLATES))
			$template_dirs[] = VTEMPLATES;
		
		#print "<pre>";
		#var_dump($this->getTemplateDir());
		$templates = array_merge(array_reverse($this->getTemplateDir()), $template_dirs);
		$templates = array_unique($templates, SORT_REGULAR);
		foreach ($templates as $i => $v) {
			if (!is_dir($v)) unset($templates[$i]);
		}
		$this->setTemplateDir( $templates );
		#var_dump($this->getTemplateDir());
		#print "</pre>";
		
		$this->cache_dir 		= PROJECT_CACHE.DS.'smarty'.DS;
		$this->compile_dir 	= PROJECT_CACHE.DS.'smarty_compile'.DS;
		$this->use_sub_dirs = true;
		$this->caching = false;
		
		if (!is_dir($this->cache_dir)) {
			mkdir($this->cache_dir);
		}
		
		if (!is_dir($this->compile_dir)) {
			mkdir($this->compile_dir);
		}
		
		$this->loadUserPlugins();
	}
	
	public function unshiftTemplateDir($dir) {
		$directories = $this->getTemplateDir();
		array_unshift($directories, $dir);
		$this->setTemplateDir($directories);
	}
	
	public function appendTemplateDirPart($part) {
		$directories = $this->getTemplateDir();
		foreach ($directories as $directory) {
			#print $directory.NL;
			if (!substr($directory, -1) == DS) {
				$directory = $directory.DS;
			}
			$directory = $directory.$part;
			$this->unshiftTemplateDir($directory);
		}
		#var_dump($this->getTemplateDir());
	}
	
	public function loadUserPlugins() {
		
		$extension_path = VLIB.DS.'Smarty'.DS.'user_plugins';
		foreach (array('function', 'modifier') as $type) {
			$prefix = 'smarty_'.$type.'_';
			
			$plugins = VSettings::f('smarty.'.$type);
			
			foreach ($plugins as $plugin) {
				
				$function = $prefix.$plugin;
				$file = $extension_path.DS.$function.'.inc.php';
				
				if (!is_file($file)) {
					VDebug::_(new VDebugMessage("Smarty plugin: $file not found."));
					continue;
				}
				
				include $file;
				
				$this->registerPlugin($type, $plugin, $function);
			}
		}
		
	}
	
}