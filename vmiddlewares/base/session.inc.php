<?php

class VMiddlewareBaseSession extends VMiddleware {
	
	public function onBeforeRoute() {
		/*if (isset($_POST["PHPSESSID"])) {
		  session_id($_POST["PHPSESSID"]);
		}
		
		session_start();*/
		$session =& VFactory::getSession();
	}
	
	public function onBeforeQuit() {
		session_write_close();
	}
}