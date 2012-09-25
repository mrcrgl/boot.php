<?php

class VMiddlewareProtectionCsrf extends VMiddleware {

	function onBeforeRoute() {
		// check for POST and Token

		$input =& VFactory::getInput();
		$session =& VFactory::getSession();

		if (strtolower($input->getMethod()) == 'post') {
			#print_r($_POST);
			#print NL;
			#print_r($_SESSION);
			$need_token = $session->get('session.csrf_token');
			$csrf_key = $session->get('session.csrf_key');
			$got_token  = $input->get($csrf_key, null, 'post');

			if ($got_token != $need_token) {
				// TODO: Error page
				VResponse::error(500, "Invalid CSRF Token received. Your request is blocked due security reasons. Please go back and try again.");
			}
		}

	}

	function onBeforePrepareResponse() {
		// generate token and assign to template

		VLoader::import('versions.utilities.password');

		$csrf_token = VPassword::create(rand(32, 64));
		$csrf_key   = VPassword::create(rand(16, 32));

		$session =& VFactory::getSession();
		#print session_id().NL;
		#printf("Old CSRF: %s : %s".NL, $session->get('session.csrf_key'), $session->get('session.csrf_token'));

		$session->set('session.csrf_token', $csrf_token);
		$session->set('session.csrf_key', $csrf_key);

		#print "<pre>";
		#var_dump($session);
		#print "</pre>";

		#print "Session new? ".(($session->isNew()) ? 'Ja' : 'Nein').NL;
		#printf("Generate CSRF: %s : %s".NL, $csrf_key, $csrf_token);
		#print_r($_SESSION);

		$document =& VFactory::getDocument();
		$document->assign('csrf_token', sprintf("<input type='hidden' name='%s' value='%s' />", $csrf_key, $csrf_token));
	}

}