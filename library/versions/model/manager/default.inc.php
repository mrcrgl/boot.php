<?php
/*
 * @build   11.09.2008
 * @project DMTp
 * @package DefaultManager
 * 
 * @author  Marc Riegel
 * @contact riegel@it-t.de
 * 
 * --
 * 
 * --
 */
abstract class VModelManagerDefault extends VObject {
  
  var $limit = "none";
  
  var $display_deleted = false;
  
  var $display_subuser = false;
  
  var $customer_filter = false;
  
  var $website_filter = false;
  
  var $hotel_filter = false;
  
  var $check_owner = false;
   
  var $ignore_object_state = false;
  
  /*
   * getObjects
   * 
   * arrResult - Array with the containing uid to initialize the object
   * strObject - Name of the Object
   * unique    - Name of the unique key
   * 
   * @return array of objects
   */
  protected function getObjects($arrResult, $strObject, $unique='uid') {
    $arrObjects = array();
    
    foreach ($arrResult as $key => $row) {
      if ( !isset($row[$unique]) ) {
        throw new Exception("getObjects failed! Unique '$unique' not found in Result-List.");
      }
      $arrObjects[$key] = new $strObject($row[$unique]);
    }
    return $arrObjects;
  }
  
  function getUserListString() {
    /*if ($this->display_subuser) {
      return Instance::f("Login")->obj->customer->all_sub_customer_string;
    } else {
      return "'".Instance::f("Login")->obj->customer_uid."'";
    }*/
  }
  
  function getOrdering() {
  	
  	if ($this->object->hasField('priority')) {
      return sprintf(" `priority` ASC");
    }
  	
  	return "none";
  }
  
  function getLimitCondition() {
  	return (isset($this->pagination)) ? $this->pagination->getLimitStatement() : $this->limit;
  }
  
  function getWhereCondition($where='') {
    if (!empty($where)) {
      $where = "($where)";
    } else {
      $where = "1";
    }
    
    if (!$this->display_deleted && !$this->ignore_object_state && $this->object->hasField('status')) {
      $where .= sprintf(" AND `status` >= %d", VSettings::f('default.min_object_state', 1));
    }
    
    if ($this->check_owner && $this->object->hasField('customer_uid')) {
      $where .= " AND `customer_uid` IN (".$this->getUserListString().") ";
    }
    
    if ($this->customer_filter && $this->object->hasField('customer_uid')) {
      $where .= " AND `customer_uid` = '".$this->customer_filter."' ";
    }
    
  	if ($this->hotel_filter && $this->object->hasField('hotel_uid')) {
      $where .= " AND `hotel_uid` = '".$this->hotel_filter."' ";
    }
    
    return $where;
  }
}
?>