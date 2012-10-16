<?php

/**
 * @desc	Load this Middleware to Start and Close PHP Sessions
 * 
 * @author	mriegel
 * @package	Versions.Middleware
 * @version	1.0
 */
class VMiddlewareBaseSession extends VMiddleware {
	
	/**
	 * onBeforeRoute()
	 * Starts new Session
	 * 
	 * @return void
	 */
	public function onBeforeRoute() {
		/*if (isset($_POST["PHPSESSID"])) {
		  session_id($_POST["PHPSESSID"]);
		}
		
		session_start();*/
		$session =& VFactory::getSession();
	}
	
	/**
	 * onBeforeQuit()
	 * Write-close new Session
	 * 
	 * @return void
	 */
	public function onBeforeQuit() {
		session_write_close();
	}
}
