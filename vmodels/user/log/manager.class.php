<?php
/**
 * DMT - Developer Moddelling Tool
 * Created on 24.07.2007
 *
 * @author Marc Riegel
 * @version 1.0
 * 
 * ---------------------------------
 * 
 * ---------------------------------
 * 
 */

class UserLogManager extends VModelManagerDefault {
  
  var $pagination;
  var $display_subuser = false;
  
  public function getAll($object, $limit=false) {
    $user_list = $this->getUserListString();
    if (!$user_list) {
      return array();
    }
    
    if (is_object($object)) {
      $object = get_class($object);
    }
    
    $object_clause = "";
    if (strlen($object) > 0) {
      $object_clause = " AND `object_type` LIKE '$object' ";
    }
    
    Instance::f('db_default')->setTable("user_log");
    Instance::f('db_default')->selectRows("uid", "`user_uid` IN (".$user_list.") $object_clause ", " `ts_create` DESC ", ($limit) ? "0,".$limit : "none");
    if (!Instance::f('db_default')->getNumRows()) {
      return array();
    }
    
    $record = array();
    while (Instance::f('db_default')->nextRecord()) {
      $record[] = Instance::f('db_default')->getRecord();
    }
    
    return $this->getObjects($record, 'UserLog');
  }
  
  public function getNumRows() {
    $user_list = $this->getUserListString();
    if (!$user_list) {
      return array();
    }
    
    Instance::f('db_default')->setTable("user_log");
    Instance::f('db_default')->selectRows("COUNT(*) AS count", "`user_uid` IN (".$user_list.") ");
    Instance::f('db_default')->nextRecord();
    return Instance::f('db_default')->f("count");
  }
  
  public function getUserListString() {
    if ($this->display_subuser) {
      return Instance::f("Login")->obj->all_sub_user_string;
    } else {
      return "'".Instance::f("Login")->obj->duid."'";
    }
  }
}
?>