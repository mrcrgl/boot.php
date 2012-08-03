<?php

class VMiddlewareProtectionCsrf extends VMiddleware {
	
	function onBeforeRoute() {
		// check for POST and Token
		
		$input =& VFactory::getInput();
		$session =& VFactory::getSession();
		
		if (strtolower($input->getMethod()) == 'post') {
			$need_token = $session->get('session.csrf_token');
			$csrf_key = $session->get('session.csrf_key');
			$got_token  = $input->get($csrf_key, null, 'post');
			
			if ($got_token != $need_token) {
				// TODO: Error page
				die("Invalid CSRF Token received");
			}
		}
		
	}
	
	function onBeforePrepareResponse() {
		// generate token and assign to template
		
		VLoader::import('versions.utilities.password');
		
		$csrf_token = VPassword::create(rand(32, 64));
		$csrf_key   = VPassword::create(rand(16, 32));
		
		$session =& VFactory::getSession();
		$session->set('session.csrf_token', $csrf_token);
		$session->set('session.csrf_key', $csrf_key);
		
		$document =& VFactory::getDocument();
		$document->assign('csrf_token', sprintf("<input type='hidden' name='%s' value='%s' />", $csrf_key, $csrf_token));
	}
	
}