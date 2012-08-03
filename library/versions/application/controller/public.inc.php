<?php

/**
 * The public controller.
 *
 * @copyright 2008 / riegel.it
 * @author Marc Riegel <mr@riegel.it>
 * @version $Revision: 1.8 $
 */
abstract class publicController extends globalController {
  
	var $arrStyleSheets = array();
	var $arrJavaScript = array();

	public function __contruct() {
    return parent::__construct();
  }
  
  protected function getArg($position) {
    return (isset($this->ctrl->_args[$position])) ? $this->ctrl->_args[$position] : false;
  }
  
	protected function setArg($position, $value, $force=false) {
    if ($this->getArg($position) !== false && $force === false) {
    	return false;
    }
		
    $this->ctrl->_args[$position] = $value;
    
		return $this->getArg($position);
  }
  
  protected function unsetArg($position) {
    unset($this->ctrl->_args[$position]);
  }
  
  protected function getPost($position) {
    return (isset($this->ctrl->_POST[$position])) ? $this->ctrl->_POST[$position] : false;
  }
  
  public function registerStyleSheet($__stylesheet) {
  	$this->arrStyleSheets[] = $__stylesheet;
  }

  public function registerJavaScript($__javascript) {
  	$this->arrJavaScript[] = $__javascript;
  }

  public function registerRobots($__robots) {
  	$this->Robots = $__robots;
  }

  public function setPagetitle($__pagetitle) {
  	$this->Pagetitle = $__pagetitle;
  }
  
  public function show() {
  	
  	if (count($this->arrStyleSheets) > 0) {
	  	
  		if (isset(ENV::$config['enableMinifier']) && ENV::$config['enableMinifier']) {
  			
		  	$files = "";
		  	foreach ($this->arrStyleSheets as $sheet) {
		  		$files .= (($files == "") ? "" : ",").$sheet.".css"; 
		  		#"   <link rel=\"stylesheet\" href=\"/static/css/".$sheet.".css\" type=\"text/css\" />\n";
		  	}
		  	$stylesheets = sprintf("<link type=\"text/css\" rel=\"stylesheet\" href=\"/min/b=static/css&amp;f=%s\" />\n", $files);
	  	
  		} else {
	  		$stylesheets = "";
		  	foreach ($this->arrStyleSheets as $sheet) {
		  		$stylesheets .= "   <link rel=\"stylesheet\" href=\"/static/css/".$sheet.".css\" type=\"text/css\" />\n";
		  	}
  		}
	  	Instance::f('smarty')->assign('_stylesheets', $stylesheets);
  	}

  	if (count($this->arrJavaScript) > 0) {
  		
  		if (isset(ENV::$config['enableMinifier']) && ENV::$config['enableMinifier']) {
  			
		  	$files = "";
		  	$javascripts = "";
		  	foreach ($this->arrJavaScript as $script) {
		  		if (preg_match('/jquery/', $files)) {
		  			$javascripts .= "<script src=\"/static/plugins/".$script.".js\" type=\"text/javascript\"></script>\n";
		  		} else {
		  			$files .= (($files == "") ? "" : ",").$script.".js";
		  		}
		  	}
		  	$javascripts .= sprintf("<script type=\"text/javascript\" src=\"/min/b=static/plugins&amp;f=%s\"></script>\n", $files);
	  	
  		} else {
	  		$javascripts = "";
		  	foreach ($this->arrJavaScript as $script) {
			  $javascripts .= "   <script src=\"/static/plugins/".$script.".js\" type=\"text/javascript\"></script>\n";
		  	}
  		}
	  	Instance::f('smarty')->assign('_javascripts', $javascripts);
  	}
  	
		Instance::f('smarty')->assign('_robots', $this->Robots);
		Instance::f('smarty')->assign('_pagetitle', $this->Pagetitle);

  	return parent::show();
  }
  
  protected function redirect($url) {
    header('Location: '.$url);
    exit;
  }
}

?>