<?php

VLoader::discover(VLIB.DS.'Smarty', true);

class VTemplateSmarty extends Smarty 
{
    
    public function __construct()
 {
        parent::__construct();
        
        $this->template_dir = ENV::$config['staticDir'].'templates/';
        $this->compile_dir     = ENV::$config['staticDir'].'templates_c/';
        $this->config_dir     = ENV::$config['staticDir'].'config/';
        $this->cache_dir         = ENV::$config['staticDir'].'cache/';
        $this->use_sub_dirs = true;
        $this->caching = false;
    }
    
}