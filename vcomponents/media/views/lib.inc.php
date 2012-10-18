<?php

class ComponentViewMediaLib extends VApplicationView 
{
  
  
  public function style()
 {
    
      $oDocument =& VFactory::getDocument();
      $oRenderer =& $oDocument->getRenderer();
      
      $oDocument->setTemplate('login.htpl');
      
  }
  
  public function yeah()
      {
      print "SDSDFGSDFG".NL;
      
  }
  
}
?>