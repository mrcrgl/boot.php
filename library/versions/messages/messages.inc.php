<?php

class VMessages extends VObject {
	/**
	 * @desc Set a form message to browser
	 * 
	 * @param string $headline
	 * @param string $message
	 * @param success|info|error $type
	 */
	static function _($headline, $message=false, $type='info') {

  	$session =& VFactory::getSession();
  	$messages = $session->get('messages', array());

  	$messages[] = array(
  		'type' => (string)$type,
  		'message' => (string)$message,
  		'headline' => (string)$headline
  	);
  	
  	$session->set('messages', $messages);
  }

  static function getMessages($leave=false) {
	
  	$session =& VFactory::getSession();
  	
  	$messages = $session->get('messages', array());

  	if (!$leave)
  		$session->set('messages', array());

  	return $messages;  	
  }
}