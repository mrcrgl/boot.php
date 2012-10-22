<?php

class BDispatcherFront extends RequestDispatcher 
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
        
        $language = BInstance::f('Language');
        
        #print "REDIRECT: No valid lang ident found. language changed to: ".$language->country_code."<br />";
        $this->headerLocation(200, '/'.$language->country_code.$_SERVER['REQUEST_URI']);
        exit;
    }
    
    
    
    //    if (!$language || !$language->isValid()) {
    if (!isset($language) || !$language->isValid()) {
        $language = $LanguageManager->getByCountryCode($lang_ident);
        if (!$language->isValid()) {
            $language = BInstance::f('Language');
            
            // -> redirect
            $this->headerLocation(200, '/'.$language->country_code.$_SERVER['REQUEST_URI']);
            exit;
        }
        
        // all fine
    }
    
    if (isset($language) && BInstance::f('Language')->country_code != $language->country_code) {
        BInstance::_new($language);
    }
    
        #$this->_args
        while (count($this->_args) && !$newView) {
            if (isset($this->arrView[($this->implode_ucfirst("", $this->_args))])) {
                // View found!
                $newView = $this->implode_ucfirst("", $this->_args);
            } elseif (isset($this->arrView[(implode("/", $this->_args))])) {
                // View found!
                $newView = implode("/", $this->_args);
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
            
            $urlrewriteManager = new URLrewriteManager();
            $urlrewrite = $urlrewriteManager->getByURL($_SERVER['REQUEST_URI']);
            if ($urlrewrite->isValid()) {
                $this->headerLocation(substr($urlrewrite->type, 0, 3), $urlrewrite->new);
                if (!$urlrewrite->clicks || $urlrewrite->clicks < 1)
                    $clicks = 1;
                else
                    $clicks = $urlrewrite->clicks+1;
                
                $urlrewrite->update(array('clicks'=>$clicks), true);
            } else {
                $this->headerLocation(404, '/'.BInstance::f('Language')->country_code.'/'.$this->_viewDefault);
                /*
                 * if (isset($_SERVER['HTTP_REFERER']) && strlen($_SERVER['HTTP_REFERER']) > 1)
                    mail('riegel@it-t.de', 'CHTV 404 on '.$_SERVER['REQUEST_URI'], 'CHTV 404 on '.$_SERVER['REQUEST_URI']."\n Referer: ".$_SERVER['HTTP_REFERER']."\n UserAgent: ".$_SERVER['HTTP_USER_AGENT']);
                 */
            }
            
        exit;
        }
        
        if (!$newView) {
            $this->headerLocation(404, '/'.BInstance::f('Language')->country_code.'/'.$this->_viewDefault);
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
    ENV::$parse['_view'] = $newView;
    
    return $newView;                
    }
    
    function implode_ucfirst($glue, $array)
                {
        foreach ($array as $key => $value) {
            $array[$key] = ucfirst($value);
        }
        
        return implode($glue, $array);
    } 
    
}