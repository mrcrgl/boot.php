<?php

/**
 * @desc      Load this Middleware to Start and Close PHP Sessions
 * 
 * @author    mriegel
 * @package   Versions.Middleware
 * @version   1.0
 */
class VMiddlewareBaseSession extends VMiddleware {
    
    /**
     * Starts new Session.
     * 
     * @return	void
     */
    public function onBeforeRoute() {
        $session =& VFactory::getSession();
    }

    /**
     * Write-close new Session.
     * 
     * @return	void
     */
    public function onBeforeQuit() {
        session_write_close();
    }
}
