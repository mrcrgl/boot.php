<?php


/**
 * Enable Middleware: Session.
 * 
 * @desc      Load this Middleware to Start and Close PHP Sessions
 * 
 * @author    mriegel
 * @package   boot.php.Middleware
 * @version   1.0
 */
class BMiddlewareBaseSession extends BMiddleware
{
    
    /**
     * Starts new Session.
     * 
     * @return void
     */
    public function onBeforeRoute()
     {
        $oSession =& BFactory::getSession();
    }

    /**
     * Write-close new Session.
     * 
     * @return void
     */
    public function onBeforeQuit()
     {
        session_write_close();
    }
}
