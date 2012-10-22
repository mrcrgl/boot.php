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

class UserManager extends BModelManagerSearch 
{
  
  var $pagination;
  var $display_subuser;
  
  public function _getAll()
  {
    $user_list = $this->getUserListString();
    if (!$user_list) {
      return array();
    }
    
    $dbo =& BFactory::getDatabase();
    $dbo->setTable("user");
    $dbo->selectRows("uid", $this->getWhereCondition("`puid` IN (".$user_list.") AND `uid` != '".Instance::f("Login")->obj->getUID()."'"), "none", (isset($this->pagination)) ? $this->pagination->getLimitStatement() : "none");
    if (!$dbo->getNumRows()) {
      return array();
    }
    
    $record = array();
    while ($dbo->nextRecord()) {
      $record[] = $dbo->getRecord();
    }
    
    return $this->getObjects($record, 'User');
  }
  
    public function getUserByMail($username, $email)
  {
    $dbo =& BFactory::getDatabase();
    $dbo->setTable("user");
    $dbo->selectRows("uid", $this->getWhereCondition("((`email` = '$email') AND (`username` = '$username'))"));
    if (!$dbo->getNumRows()) {
      return false;
    }
    $dbo->nextRecord();
    $objUser = new User($dbo->f('uid'));
    if (is_object($objUser) && $objUser->isValid()) {
      return $objUser;
    }
    return false;
  }
  
  public function getAllByCustomer(Customer $Customer)
  {
    $dbo =& BFactory::getDatabase();
    $dbo->setTable("user");
    $dbo->selectRows("uid", "`customer_uid` = '".$Customer->uid."'", "none", (isset($this->pagination)) ? $this->pagination->getLimitStatement() : "none");
    if (!$dbo->getNumRows()) {
      return array();
    }
    
    $record = array();
    while ($dbo->nextRecord()) {
      $record[] = $dbo->getRecord();
    }
    
    return $this->getObjects($record, 'User');
  }
  
  
  public function getNumRows()
  {
    $user_list = $this->getUserListString();
    if (!$user_list) {
      return 0;
    }
    
    $dbo =& BFactory::getDatabase();
    $dbo->setTable("user");
    $dbo->selectRows("COUNT(*) AS count", $this->getWhereCondition("`puid` IN (".$user_list.")"));
    $dbo->nextRecord();
    return $dbo->f("count");
  }
  
  
  public function doLogin($username, $password)
  {
    $dbo =& BFactory::getDatabase();
    $dbo->setTable("user");
    $dbo->selectRows("uid", $this->getWhereCondition("((`email` = '$username') OR (`username` = '$username')) AND `password` = MD5('$password')"));
    if (!$dbo->getNumRows()) {
      return false;
    }
    $dbo->nextRecord();
    $objUser = new User($dbo->f('uid'));
    if (is_object($objUser) && $objUser->isValid()) {
      $objUser->log('Login', "User logged in Successful.");
      return $objUser;
    }
    return false;
  }
  
  public function getCompleteSubUserList($user_uid=false, $user_list=false)
  {
    if ($user_uid === false) {
      $user_uid = Instance::f('Login')->obj->duid;
    }
    if (!is_array($user_list)) {
      $user_list = array($user_uid);
    }
    $dbo =& BFactory::getDatabase();
    $dbo->setTable("user");
    $dbo->selectRows("uid", $this->getWhereCondition("`puid` = '$user_uid'"));
    if ($dbo->getNumRows()) {
      $record = array();
      while ($dbo->nextRecord()) {
        $record[] = $dbo->f("uid");
      }
      
      while ($next_uid = array_shift($record)) {
        $user_list[] = $next_uid;
        $user_list = $this->getCompleteSubUserList($next_uid, $user_list);
      }
    }
    return $user_list;
  }
  
  public function getCompleteSubUserListString()
  {
    $user_list = $this->getCompleteSubUserList();
    $user_list_string = "";
    foreach ($user_list as $uid) {
      $user_list_string .= (($user_list_string == "") ? "" : ", ")."'$uid'";
    }
    return $user_list_string;
  }
  
  public function getCompleteSubUserListObjects()
  {
    $record = array();
    foreach ($this->getCompleteSubUserList() as $uid) {
      $record[] = array('uid' => $uid);
    }
    return $this->getObjects($record, 'User');
  }
  
  public function getSubUserList()
  {
    $dbo =& BFactory::getDatabase();
    $dbo->setTable("user");
    $dbo->selectRows("uid", $this->getWhereCondition("`puid` = '$user_uid'"));
    if ($dbo->getNumRows()) {
      while ($dbo->nextRecord()) {
        $next_uid = $dbo->f("uid");
        $user_list[] = $next_uid;
      }
    }
    return $user_list;
  }
  
  public function getSubUserListString()
  {
    $user_list = $this->getSubUserList();
    $user_list_string = "";
    foreach ($user_list as $uid) {
      $user_list_string .= (($user_list_string == "") ? "" : ", ")."'$uid'";
    }
    return $user_list_string;
  }
  
  public function getSubUserListObjects()
  {
    $record = array();
    foreach ($this->getSubUserList() as $uid) {
      $record[] = array('uid' => $uid);
    }
    return $this->getObjects($record, 'User');
  }
  
  public function safeMode($owner_uid, $user_uid=false, $user_list=false)
  {
    if ($user_uid === false) {
      $user_uid = Instance::f('Login')->obj->duid;
    }
    if (!is_array($user_list)) {
      $user_list = array($user_uid);
    }
    $dbo =& BFactory::getDatabase();
    $dbo->setTable("user");
    $dbo->selectRows("uid", $this->getWhereCondition("`puid` = '$user_uid'"));
    if ($dbo->getNumRows()) {
      $record = array();
      while ($dbo->nextRecord()) {
        $record[] = $dbo->f("uid");
      }
      
      while ($next_uid = array_shift($record)) {
        $user_list[] = $next_uid;
        $user_list = $this->safeMode($owner_uid, $user_uid, $user_list);
        if (in_array($owner_uid, $user_list)) {
          return true;
        }
      }
    }
    return false;
  }
}
?>