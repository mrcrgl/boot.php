<?php


class BApplicationRouter 
{
    
    
    public function route()
 {
        
        // trigger event onBeforeRoute
        BMiddleware::trigger('onBeforeRoute');
        
        $controller =& BFactory::getController();

        $controller->handleRequest();
        
        // trigger event onAfterRoute
        BMiddleware::trigger('onAfterRoute');
        
    }
    
    
}