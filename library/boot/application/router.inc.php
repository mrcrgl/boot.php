<?php


class BApplicationRouter
{
    
    
    public function route()
    {
        
        $this->_input();
        
        if (BComponentLeader::count() > 0) {
            
            #BComponentLeader::reset();
            
            while ($oComponent =& BComponentLeader::walk()) {
                print "Running...".NL;
                
                
                $this->_process($oComponent);
            }
        } else {
            print "Throw 500".NL;
            BResponse::error(500);
        }
        
        $this->_output();
        
        // trigger event onBeforeRoute
        /*BMiddleware::trigger('onBeforeRoute');
        
        $controller =& BFactory::getController();

        $controller->handleRequest();
        
        // trigger event onAfterRoute
        BMiddleware::trigger('onAfterRoute');*/
        
        #
        
        
    }
    
    protected function _input()
    {
        print "Input called.".NL;
        $oInput = BFactory::getInput();
        $oInput->collect();
    }
    
    protected function _process(BComponent $oComponent)
    {
        print "Preprocessing Component.".NL;
        
        if (BComponentLeader::isLast()) {
            $oComponent->set('_bExecute', true);
        }
        
        print "<pre>";
        var_dump($oComponent);
        print "</pre>";
        
        $oComponent->callController();
        
    }
    
    protected function _output()
    {
        print "Output called.".NL;
    }
}