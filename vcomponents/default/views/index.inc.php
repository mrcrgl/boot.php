<?php

class ComponentDefaultViewIndex extends BApplicationView 
{
  
  
  public function show()
 {
    
      $oDocument =& BFactory::getDocument();
      $oRenderer =& $oDocument->getRenderer();
      
      $oDocument->setTemplate('index.htpl');
      
  }
  
}

