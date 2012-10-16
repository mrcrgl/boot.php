<?php

/**
 * @desc	Load this Middleware to enable CSRF Protection
 *
 * @author	mriegel
 * @package	Versions.Middleware
 * @version	1.0
 */
class VMiddlewareProtectionCsrf extends VMiddleware {

	/**
	 * onBeforeRoute()
	 * Checks on request method POST the csrf token, if it doesnt compare set reponsecode to 500
	 * 
	 * @return void
	 */
	function onBeforeRoute() {
		$input =& VFactory::getInput();
		$session =& VFactory::getSession();

		if (strtolower($input->getMethod()) == 'post') {
			
			$need_token = $session->get('session.csrf_token');
			$csrf_key = $session->get('session.csrf_key');
			$got_token  = $input->get($csrf_key, null, 'post');

			if ($got_token != $need_token) {
				// Go to error page
				VResponse::error(500, "Invalid CSRF Token received. Your request is blocked due security reasons. Please go back and try again.");
			}
		}

	}

	/**
	 * onBeforePrepareResponse()
	 * Generate new CSRF token, store it to session and assign to template
	 * 
	 * @return void
	 */
	function onBeforePrepareResponse() {
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
