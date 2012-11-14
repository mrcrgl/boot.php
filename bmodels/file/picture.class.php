<?php
/*
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

class FilePicture extends BModelFile 
{
  /*
   * 'Picture', Bilder
   */
    var $_DataMap = array(
    '_head'       => 'file_picture',
    '_uid'        => 'uid',
    '_usehexuid'  => true,
    'default'     => array(
      '_type'     => 'all-in-a-row',
      '_table'    => 'file_picture',
      '_unique'   => 'uid',
      '_mapping'  => array(
        'uid'             => 'uid',
                'path'                        => 'path',
                'filename'                => 'filename',
                'size'                        => 'size',
                'filetype'                => 'filetype',
                'ts_create'         => 'ts_create',
        'ts_update'              => 'ts_update',
                'status'                    => 'status'
      ),
      '_specials' => array(
        'ts_create'   => 'oncreate:mysqltimestamp',
        'ts_update'   => 'onupdate:mysqltimestamp'
      )
    ),
    '_database'  => 'default',
    '_datatypes'  => array()
  );

  var $_DataRules = array(
      'path'                          => array(true,   '^.{1,128}$',     'default'),
      'filename'                  => array(true,   '^.{1,128}$',     'default'),
      'size'                          => array(true,   '^.{1,128}$',     'default'),
      'filetype'                  => array(true,   '^.{1,128}$',     'default'),
        'status'                         => array(true,   '^(1|0)$',                 'default')
  );
    
  var $_AllowedMime = array('image/jpeg', 'image/png', 'image/gif');
  
    public function __get($__memberName)
  {
    if ($__memberName == 'status_string') {
      if ($this->status == 1) {
        return BText::_('state_active');
      }
      if ($this->status == 0) {
        return BText::_('state_inactive');;
      }
    }
    
    return parent::__get($__memberName);
  }
  
  public function update($param, $dontCheckNeedles=false)
  {
          
      return parent::update($param, $dontCheckNeedles);
  }
  
}
?>
