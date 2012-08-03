<?php
/*
 * @build   21.09.2009
 * @project 
 * @package DefaultObject
 * 
 * @author  Marc Riegel
 * @contact mr@riegel.it
 * 
 * --
 * 
 * --
 */
abstract class VModelConnectorLaw extends VModelConnector {
  
  private $arrUsers = array();
  
  private $boolPermit = false;
  
  public function __construct($attributes=false) {
    
    $this->arrUsers = array();
    $this->arrUsers[] = Instance::f('Login')->obj->duid;
    
    foreach (split("'", Instance::f('Login')->obj->all_sub_user_string) as $possible_uid) {
      if (Validator::is($possible_uid, 'uid')) {
        $this->arrUsers[] = $possible_uid;
      }
    }
    
    parent::__construct($attributes);
  }
  
  public function __get($__memberName) {
    if ($this->boolPermit == false && $this->isValid() && Validator::is(parent::__get('customer_uid'), 'uid')) {
      if (!in_array(parent::__get('user_uid'), $this->arrUsers)) {
        Instance::f('smarty')->assign('error_object_permission-denied', true);
        $this->destroy();
      } else {
        $this->boolPermit = true;
      }
    }
    
    return ($this->boolPermit) ? parent::__get($__memberName) : "";
  }
  
  /**
   * Destroys a Object.
   */
  private function destroy() {
    $classname = get_class($this);
    
    throw new Exception("permission_denied_by_object: $classname");
    
    #$this = new $classname();
    #$this = null;
    #settype($this, 'null');
    #unset($this);
  }
  
}
?>