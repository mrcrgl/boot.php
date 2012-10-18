<?php


class VApplicationRouter 
{
    
    
    public function route()
 {
        
        // trigger event onBeforeRoute
        VMiddleware::trigger('onBeforeRoute');
        
        $controller =& VFactory::getController();

        $controller->handleRequest();
        
        // trigger event onAfterRoute
        VMiddleware::trigger('onAfterRoute');
        
    }
    
    
}