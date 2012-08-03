<?php
/**
 * DMT - Developer Moddelling Tool
 * Created on 09.01.2007
 *
 * @author Sven Mittreiter
 * @version 1.0
 * 
 * ---------------------------------
 * 
 * ---------------------------------
 * 
 */
class VIEWLogout extends publicController {
  
  public function prepare() { 
    return parent::prepare();
  }
  
  public function proceed() {
    Instance::f('Login')->doLogout();
    
    return parent::proceed();
  }
  
  public function show() {
    $this->setTemplate('Login/logout');
    
    return parent::show();
  }
}
?>
