<?php


class BApplicationRouter
{
    
    
    public function route()
    {
        BMiddleware::trigger('onBeforeRoute');
        
        
        $this->_input();
        
        if (BComponentLeader::count() > 0) {
            
            #BComponentLeader::reset();
            
            while ($oComponent =& BComponentLeader::walk()) {
                #print "Running...".NL;
                /*var_dump($oComponent);
                printf(
                    "--".DS.
                    "Component: %s".DS.
                    "View: %s".DS.
                    "Method: %s".DS.DS,
                    get_class($oComponent),
                    $oComponent->getRequestView(),
                    $oComponent->getRequestMethod()
                );*/
                
                $this->_process($oComponent);
            }
        } else {
            #print "Throw 500".NL;
            BResponse::error(500, "No components to walk through.");
        }
        
        $this->_output();
        
        // trigger event onBeforeRoute
        /*BMiddleware::trigger('onBeforeRoute');
        
        $controller =& BFactory::getController();

        $controller->handleRequest();
        
        // trigger event onAfterRoute
        BMiddleware::trigger('onAfterRoute');*/
        
        #
        
        BMiddleware::trigger('onAfterRoute');
    }
    
    protected function _input()
    {
        #print "Input called.".NL;
        $oInput = BFactory::getInput();
        $oInput->collect();
    }
    
    protected function _process(BComponent $oComponent)
    {
        #print "Preprocessing Component.".NL;
        
        if (BComponentLeader::isLast()) {
            $oComponent->set('_bExecute', true);
        }
        
        #print "<pre>";
        #var_dump($oComponent);
        #print "</pre>";
        
        $oComponent->callController();
        
    }
    
    protected function _output()
    {
        #print "Output called.".NL;
    }
}