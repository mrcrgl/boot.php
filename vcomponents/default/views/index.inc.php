<?php

class ComponentDefaultViewIndex extends VApplicationView 
{
  
  
  public function show()
 {
    
      $oDocument =& VFactory::getDocument();
      $oRenderer =& $oDocument->getRenderer();
      
      $oDocument->setTemplate('index.htpl');
      
  }
  
}

