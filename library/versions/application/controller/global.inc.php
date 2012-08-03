<?php

// Start with time measurement here
$time_start = microtime(TRUE);

/**
 * The global controller. All other controllers should extend of this one.
 *
 * @copyright 2008 / riegel.it
 * @author Marc Riegel <mr@riegel.it>
 * @version $Revision: 1.8 $
 */
abstract class globalController {

  /**
   * The controller Object
   *
   * @var ctrl
   */
  protected $ctrl;
  
  /**
   * The $_SERVER array
   *
   * @var _SERVER
   */
  protected $_SERVER = array();
  
  /**
   * The $_POST array
   *
   * @var _POST
   */
  protected $_POST = array();
  
  /*
   * Basic Template to display
   */
  protected $strBasicTemplate = 'page.htpl';
  
  public function __contruct() {
    $this->checkLanguage();
  }
  
  public function __get($__memberName) {
    if ($__memberName == 'isSearchEngine' || $__memberName == 'isSE') {
      return $this->checkSE();
    } else {
      return '';
    }
  }
  
  function setDispatcher($_dispatcher) {
    $this->ctrl = $_dispatcher;
  }
  
  public function changeView($_view, $bCache=FALSE) {
    //
  }
  
  public function prepare() {
    
  	return 'proceed';
  }
  
  public function proceed() {
    return 'show';
  }
  
  public function show() {
    
  	Instance::f('smarty')->assign('messages', $this->getMessages());
  	
  	if (strtolower($this->getArg('display')) == 'iframe') {
  		$this->setBasicTemplate('iframe.htpl');
  	}
  	
  	$array = explode('.', $this->strBasicTemplate);
  	Instance::f('smarty')->assign('_display_mode', array_shift($array));
  	
  	if (isset($this->_template)) {
      Instance::f('smarty')->assign('_display', $this->_template.'.htpl');
      print $this->renderHTML( Instance::f('smarty')->fetch($this->strBasicTemplate) );
      
    }
    
    return 'finish';
  }
  
  public function message($headline, $message=false, $type='info') {
		Message::_($headline, $message, $type);
  }

  public function getMessages($leave=false) {
		return Message::getMessages($leave);
  }
  
  
  private function renderHTML($html) {
  	
  	if (preg_match_all('!<img(.*)\/>!', $html, $matches)) {
  		
  		foreach ($matches[0] as $match) {
  			
  			#print $match."\n";
  			$src 		= false;
  			$width 	= false;
  			$height = false;
  			
  			if (preg_match('!src=\"([^"]+)\"!', $match, $msrc)) {
  				$src = $msrc[1];
  			}
  			if (preg_match('!width=\"([0-9][^"]+)\"!', $match, $mwidth)) {
  				$width = $mwidth[1];
  			}
  			if (preg_match('!height=\"([0-9][^"]+)\"!', $match, $mheight)) {
  				$height = $mheight[1];
  			}
  			
  			if (!$src) continue;
  			if (!$width && !$height) continue;
  			
  			$src_new = $src;
  			#print "src=$src; width=$width; height=$height\n";
  			
  			if (!preg_match('!\.(png|jpg|gif|jpeg|bmp)$!', $src_new, $extension)) continue; 
  			
  			$src_new = str_replace($extension[0], sprintf("|%s|%s%s", $width, $height, $extension[0]), $src);
  			
  			#print "src neu: ".$src_new;
  			 	
  			$html = str_replace($src, $src_new, $html);
  		}
  		
  		
  		#var_dump($matches);
  	}
  	
  	#exit;
  	
  	return $html;
  }
  
  public function setTemplate($template) {
    $this->_template = $template;
  }
  
  public function setBasicTemplate($template) {
    $this->strBasicTemplate = $template;
  }
  
  protected final function checkSE() {
    $searchEngines = array(
      'fast',
      'googlebot',
      'infoseek',
      'mediapartners-google',
      'msnbot',
      'netnose',
      'nusearch',
      'seekbot',
      'slurp'
    );
    $this->isSearchEngine = FALSE;
    foreach($searchEngines AS $userAgent) {
      strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), $userAgent) !== FALSE ? $this->isSearchEngine = TRUE : FALSE;
    }
  }
  
  protected function loadLanguage() {
    #Instance::_new('lang', new Language());
  }
  
  protected function checkLanguage() {
    #ENV::$userLanguage = 'de';
    #return ENV::$userLanguage;
  }
  
  

  protected final function getCurrentBrowserSignature() {
    return @sha1($_SERVER['HTTP_ACCEPT_CHARSET'] . $_SERVER['HTTP_ACCEPT_ENCODING'] . $_SERVER['HTTP_ACCEPT_LANGUAGE'] . $_SERVER['HTTP_USER_AGENT']);
  }
  
  public function __toString() {
  	return get_class($this);
  }
}
