<?php

class ComponentDefaultViewIndex extends VApplicationView {
  
  
  public function show() {
    
  	$document =& VFactory::getDocument();
  	$renderer =& $document->getRenderer();
  	
  	$document->setTemplate('index.htpl');
  	
  }
  
}

