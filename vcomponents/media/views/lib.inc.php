<?php

class ComponentViewMediaLib extends BApplicationView 
{
  
  
  public function style()
 {
    
      $oDocument =& BFactory::getDocument();
      $oRenderer =& $oDocument->getRenderer();
      
      $oDocument->setTemplate('login.htpl');
      
  }
  
  public function yeah()
      {
      print "SDSDFGSDFG".NL;
      
  }
  
}
?>