<?php

VLoader::import('versions.application.dispatcher.default');
VLoader::discover(VROOT.DS.'models'.DS.'language');

class VDispatcherBootstrap extends RequestDispatcher 
{
    
    function getRequestView()
 {
    
        $newView     = '';
    $newArgs     = array();
    $viewFound   = false;
    $argKey      = false;
    $n           = 0;
    
    if ($this->_args[0] == "") {
        array_shift($this->_args);
    }
    
    #print implode('", "', $this->_args);
    
        
    if (count($this->_args) < 1) {
        // to few arguments
        // -> redirect
        print "To few arguments<br />";
        $language = $LanguageManager->getSystemDefault();
        $this->headerLocation(200, '/'.$language->country_code.$_SERVER['REQUEST_URI']);
        exit;
    }
    
    $LanguageManager = new LanguageManager();
    
    $lang_ident = array_shift($this->_args);
    #print "lang ident received: $lang_ident<br />";
    if (!preg_match('/^[a-z]{2}$/', $lang_ident)) {
        // langugae not set
        // -> redirect with langugae
        
        $language =& VFactory::getLanguage();
        
        #print "REDIRECT: No valid lang ident found. language changed to: ".$language->country_code."<br />";
        $this->headerLocation(200, '/'.$language->country_code.$_SERVER['REQUEST_URI']);
        exit;
    }
    
    
    
    //    if (!$language || !$language->isValid()) {
    if (!isset($language) || !$language->isValid()) {
        $language = $LanguageManager->getByCountryCode($lang_ident);
        if (!$language->isValid()) {
            $language = VInstance::f('Language');
            
            // -> redirect
            $this->headerLocation(200, '/'.$language->country_code.$_SERVER['REQUEST_URI']);
            exit;
        }
        
        // all fine
    }
    
    if (isset($language) && VFactory::getLanguage()->country_code != $language->country_code) {
        VInstance::_new($language);
    }
    
        #$this->_args
        $NavigationPointManager = new NavigationPointManager();
        
        while (count($this->_args) && !$newView) {
            
            $navp = $NavigationPointManager->getByLink(implode("/", $this->_args));
            
            if ($navp->isValid()) {
                // View found!
                $newView = $navp->view;
            } else {
                
                $newArgs[$n] = array_pop($this->_args);
        
        if ($argKey) {
                  if (isset($part)){
                    $newArgs[$argKey] = $part;
                  }
          $argKey = false;
        } else {
          $argKey = $newArgs[$n];
        }
                
        $n++;
            }
        }
        
        /**
         * TODO
         * 404 Detection is broken due Detection of visiting /
         */
    
        if (!$newView && count($this->_args) < 1) {
            
            $this->headerLocation(404, '/'.VFactory::getLanguage()->country_code.'/'.$this->_viewDefault);
            
        exit;
        }
        
        if (!$newView) {
            $this->headerLocation(404, '/'.VInstance::f('Language')->country_code.'/'.$this->_viewDefault);
            /*
             * if (isset($_SERVER['HTTP_REFERER']) && strlen($_SERVER['HTTP_REFERER']) > 1)
                mail('riegel@it-t.de', 'CHTV 404 (2nd) on '.$_SERVER['REQUEST_URI'], 'CHTV 404 on '.$_SERVER['REQUEST_URI']."\n Referer: ".$_SERVER['HTTP_REFERER']."\n UserAgent: ".$_SERVER['HTTP_USER_AGENT']);
             */
            exit;
        }
    
    /*
     * Overwrite old Arguments
     */
    $this->_args = array_reverse($newArgs);
    #print_r( array_reverse($newArgs) );
    /*
     * to smarty<
     */
    #ENV::$parse['_view'] = $newView;
    
    return $newView;                
    }
    
    public function changeView($_view, $bCache=FALSE)
                {
        if (isset($this->_view)) {
            if ($bCache == TRUE) {
                $this->_viewCached = &$this->_view;
            }

            unset($this->_view);
        }

        /*
        $_view = (!isset($this->arrView[$_view]) && isset($this->arrView[strtolower($_view)])) ? strtolower($_view) : $_view;

        if (!$this->checkLoginRequired($_view)) {
            return false;
        }

        if (!$this->checkPermission($_view)) {
            return false;
        }
        */
        $this->_view = new $_view();
        
        $this->_view->setDispatcher($this);
        
        #VInstance::_new(&$this->_view, 'view');
        
        return $this->_view;
    }
    
    function implode_ucfirst($glue, $array)
    {
        foreach ($array as $key => $value) {
            $array[$key] = ucfirst($value);
        }
        
        return implode($glue, $array);
    } 
    
}