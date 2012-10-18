<?php

VLoader::import('versions.base.object');

abstract class VMiddleware extends VObject 
{
    
    static $_enabled = null;
    
    static public function trigger($__event)
    {
        
        if (!self::$_enabled) {
            self::loadMiddleware();
        }
        
        foreach (self::$_enabled as $middleware) {
            $middleware->$__event();
        }
        
    }
    
    static function loadMiddleware()
        {
        
        self::$_enabled = array();
        
        $enabled_names = VSettings::f('middleware.enable');
        
        foreach ($enabled_names as $enabled_name) {
            $parts = explode('.', $enabled_name);
            $ucfparts = array();
            foreach ($parts as $i => $part) {
                $ucfparts[$i] = ucfirst($part);
            }
            
            $classname = 'VMiddleware'.implode('', $ucfparts);
            
            $path_tail = implode(DS, $parts).'.inc.php';
            
            if (is_file(PROJECT_MIDDLEWARES.DS.$path_tail)) {
                $file = PROJECT_MIDDLEWARES.DS.$path_tail;
            } else if (is_file(VMIDDLEWARES.DS.$path_tail)) {
                $file = VMIDDLEWARES.DS.$path_tail;
            } else {
                VDebug::_(new VDebugMessage("Middleware $enabled_name not found."));
                continue;
            }
            
            
            VLoader::file($file);
            
            self::$_enabled[$enabled_name] = new $classname();
        }
    }
    
    function onBeforeRoute()
        {
        
    }
    
    function onBeforePrepareRequest()
        {
        
    }
    
    function onAfterPrepareRequest()
        {
        
    }
    
    function onBeforeProcessRequest()
        {
        
    }
    
    function onBeforePrepareView()
        {
        
    }
    
    function onAfterPrepareView()
        {
        
    }
    
    function onBeforeProcessView()
        {
        
    }
    
    function onAfterProcessView()
        {
        
    }
    
    function onBeforeCleanupView()
        {
        
    }
    
    function onAfterCleanupView()
        {
        
    }
    
    function onAfterProcessRequest()
        {
        
    }
    
    function onBeforePrepareResponse()
        {
        
    }
    
    function onAfterPrepareResponse()
        {
        
    }
    
    function onBeforePrintResponse()
        {
        
    }
    
    function onAfterPrintResponse()
        {
        
    }
    
    function onBeforeQuit()
        {
        
    }
    
}