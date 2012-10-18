<?php

class VMessages extends VObject 
{
    /**
     * @desc Set a form message to browser
     * 
     * @param string $headline
     * @param string $message
     * @param success|info|error $type
     */
    static function _($headline, $message=false, $type='info')
     {

      $oSession =& VFactory::getSession();
      $messages = $oSession->get('messages', array());

      $messages[] = array(
          'type' => (string)$type,
          'message' => (string)$message,
          'headline' => (string)$headline
      );
      
      $oSession->set('messages', $messages);
  }

  static function getMessages($leave=false)
  {
    
      $oSession =& VFactory::getSession();
      
      $messages = $oSession->get('messages', array());

      if (!$leave)
          $oSession->set('messages', array());

      return $messages;      
  }
}