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

class UserLog extends BModelConnector 
{
  
  var $_DataMap = array(
    '_head'       => 'user_log',
    '_uid'        => 'uid',
    '_usehexuid'  => true,
    'default'    => array(
      '_type'     => 'all-in-a-row',
      '_table'    => 'user_log',
      '_unique'   => 'uid',
      '_mapping'  => array(
        'uid'          => 'uid',
        'user_uid'     => 'user_uid',
        'project_name' => 'project_name',
        'object_type'  => 'object_type',
        'object_uid'   => 'object_uid',
        'remote_addr'  => 'remote_addr',
        'message'      => 'message',
        'ts_create'    => 'ts_create',
      ),
      '_specials' => array(
        'ts_create'   => 'oncreate:unixtimestamp'
      )
    ),
    '_database'  => 'default'
  );
  
  var $_DataRules = array(
    'user_uid'      => array(true,  '[0-9a-zA-Z]{13}',   'default'),
    'project_name'  => array(false, '[0-9a-zA-Z]{1,32}', 'default'),    
    'object_type'   => array(false, '[0-9a-zA-Z]{1,32}', 'default'),
    'object_uid'    => array(false, '[0-9a-zA-Z]{13}',   'default'),
    'remote_addr'   => array(false, '[0-9a-zA-Z]{1,32}', 'default'),
    'message'       => array(false, '[0-9a-zA-Z]{1,128}','default')
  );
  
  var $refUser;
  
  public function __construct($attributes=false)
  {
    parent::__construct($attributes);
  }
  
  public function __get($__memberName)
  {
    if ($__memberName == 'user') {
      if (!$this->refUser) {
        $this->refUser = new User($this->user_uid);
      }
      return $this->refUser;
    }
    return parent::__get($__memberName);
  }
  
  
}
?>
