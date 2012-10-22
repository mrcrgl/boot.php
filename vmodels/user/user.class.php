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

class User extends UserAbstraction 
{
  
  var $_DataMap = array(
    '_head'       => 'user',
    '_uid'        => 'uid',
    '_usehexuid'  => true,
    '_safemode'   => false,
    'default'    => array(
      '_type'     => 'all-in-a-row',
      '_table'    => 'user',
      '_unique'   => 'uid',
      '_mapping'  => array(
        'uid'          => 'uid',
        'puid'         => 'puid',
        'duid'         => 'duid',
              'group_uid'         => 'group_uid',
              'language_uid' => 'language_uid',
              'customer_uid' => 'customer_uid',
              'salutation'   => 'salutation',
        'firstname'    => 'firstname',
        'lastname'     => 'lastname',
        'username'     => 'username',
        'email'        => 'email',
        'password'     => 'password',
        'ts_create'    => 'ts_create',
        'ts_update'    => 'ts_update',
        'status'       => 'status'
      ),
      '_specials' => array(
        'ts_create'   => 'oncreate:unixtimestamp',
        'ts_update'   => 'onupdate:unixtimestamp'
      )
    ),
    '_database'  => 'default'
  );
  
  var $_DataRules = array(
    'language_uid'      => array(false, '^[0-9a-f]{13}$',          'default'),
    'customer_uid'      => array(true,  '^[0-9a-f]{13}$',          'default'),
    'group_uid'                    => array(true,  '^[0-9a-f]{13}$',          'default'),
    'salutation'        => array(false, '^(mister|misses)$',       'default'),
    'firstname'         => array(false, '^[a-zA-Z]{1,32}$',        'default'),
    'lastname'          => array(false, '^[a-zA-Z]{1,32}$',        'default'),
    'email'             => array(true,  '^.{1,255}$',    'default'),
    'password'          => array(false, '^[0-9a-zA-Z]{32}$',       'default'),
    'username'          => array(false, '^.{1,255}$',    'default'),
    'puid'              => array(false, '^[0-9a-z]{13}$',          'default'),
    'duid'              => array(false, '^[0-9a-z]{13}$',          'default'),
    'status'            => array(true,  '^[0-9]{1}$',              'default')
  );
  
  private $refParent;
  private $refCustomer;
  
  
  public function __construct($attributes=false)
  {
    parent::__construct($attributes);
  }
  
  public function __get($__memberName)
  {
    if ($__memberName == 'duid') {
      if (strlen(parent::__get($__memberName)) < 1) {
          return $this->uid;
      }
    }
    if ($__memberName == 'is_copy') {
      return ($this->duid == $this->uid) ? false : true;
    }
    if ($__memberName == 'fullname') {
      if (strlen($this->firstname) > 1 || strlen($this->lastname) > 1) {
        return $this->firstname." ".$this->lastname;
      }
      if (strlen($this->username) > 1) {
        return $this->username;
      }
      return $this->email;
    }
    if ($__memberName == 'fullname_with_salutation') {
        return (($this->salutation) ? Text::_($this->salutation).' '.$this->fullname : $this->fullname);
    }
    if ($__memberName == 'status_string') {
      if ($this->status == 1) {
        return "aktiv";
      }
      if ($this->status == 0) {
        return "inaktiv";
      }
      if ($this->status == -1) {
        return "gesperrt";
      }
    }
    if ($__memberName == 'all_sub_user') {
      $refManager = new UserManager();
      return $refManager->getCompleteSubUserListObjects();
    }
    if ($__memberName == 'sub_user') {
      $refManager = new UserManager();
      return $refManager->getSubUserListObjects();
    }
    if ($__memberName == 'all_sub_user_string') {
      $refManager = new UserManager();
      return $refManager->getCompleteSubUserListString();
    }
    if ($__memberName == 'sub_user_string') {
      $refManager = new UserManager();
      return $refManager->getSubUserListString();
    }
    if ($__memberName == 'parent_user') {
      if (!is_object($this->refParent)) {
        if ($this->puid) {
          $this->refParent = new User($this->puid);
        } else {
          $this->refParent = new User();
        }
      }
      return $this->refParent;
    }
    if ($__memberName == 'customer') {
      if (!is_object($this->refCustomer)) {
        if ($this->customer_uid) {
          $this->refCustomer = new Customer($this->customer_uid);
        } else {
          $this->refCustomer = new Customer();
        }
      }
      return $this->refCustomer;
    }
    return parent::__get($__memberName);
  }
  
  public function update($param, $dontCheckNeedles=false)
  {
    
    if ($param['duid'] == "is_parent") {
        $param['duid'] = $param['puid'];
    } else {
        $param['duid'] = "";
    }
        
    /*
     * Password verification
     */
    if (isset($param['password']) && strlen($param['password']) > 1 && isset($param['password_retype'])) {
      if ($param['password'] != $param['password_retype']) {
        $this->setErrorMsg('password_retype_err');
        Instance::f('smarty')->assign('password_retype_err', '1');
        return false;
      } else {
        $param['password'] = md5($param['password_retype']);
      }
    } else {
      if (isset($param['password'])) { unset($param['password']); }
    }
    
    if ($this->isValid() && $this->uid == Instance::f('Login')->obj->getUID()) {
      // Update der eigenen Daten
      $param2 = $param;
      unset($param);
      if (isset($param2['password'])) {
        $param['password'] = $param2['password'];
      }
      $dontCheckNeedles = true;
    }
    
    
    return parent::update($param, $dontCheckNeedles);
  }
  
  public function delete()
  {
    
    $bOk = $this->update(array('status' => -9), true);
    
    if (!$bOk) {
      return false;
    }
    
    Instance::f('Login')->obj->log($this, "Object removed.");
    return true;
  }
  
  public function hasPermission($permission_ident, $explicit=false)
  {
    if ($this->group && $this->group->isValid()) {
        return $this->group->hasPermission($permission_ident, $explicit);
    }
      return false;
      

    // obsolete..
    $perm_list = ($explicit == false) ? "'$permission_ident', 'ui.super'" : "'$permission_ident'";

    Instance::f('db_default')->setTable("user_permissions");
    Instance::f('db_default')->selectRows("*", "`user_uid` = '".$this->uid."' AND `permission_ident` IN (".$perm_list.") AND `have` = '1'");
    if (!Instance::f('db_default')->getNumRows()) {
      return false;
    }
    return true;
  }
  
  public function log($object, $message)
  {
    $remote_addr = $_SERVER['REMOTE_ADDR'];
    $object_uid  = '';
    if (is_object($object)) {
      $object_type = get_class($object);
    }
    if (is_object($object) && in_array('isValid', get_class_methods($object)) && $object->isValid()) 
{
      $object_uid = $object->uid;
    }
    if (!isset($object_type) && strlen($object) > 0) {
      $object_type = $object;
    }
    
    $refLog = new UserLog();
    $refLog->update(
      array(
        'user_uid'    => $this->uid,
        'project_name'=> BSettings::f('default.title'),
        'object_type' => $object_type,
        'object_uid'  => $object_uid,
        'remote_addr' => $remote_addr,
        'message'     => $message
      )
    );
    
    unset($refLog);
    
    return true;
  }
  
}
?>
