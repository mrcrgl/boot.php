<?php

class ComponentViewMediaLib extends VApplicationView 
{
  
  
  public function style()
 {
    
      $document =& VFactory::getDocument();
      $renderer =& $document->getRenderer();
      
      $document->setTemplate('login.htpl');
      
  }
  
  public function yeah()
      {
      print "SDSDFGSDFG".NL;
      
  }
  
}
?>