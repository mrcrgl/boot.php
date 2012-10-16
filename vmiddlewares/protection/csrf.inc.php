<?php
/**
 * Enable Middleware: CSRF.
 *
 * @desc      Load this Middleware to enable CSRF Protection
 *
 * @author    mriegel
 * @package   Versions.Middleware
 * @version   1.0
 */

/**
 * Enable Middleware: CSRF.
 *
 * @desc      Load this Middleware to enable CSRF Protection
 *
 * @author    mriegel
 * @package   Versions.Middleware
 * @version   1.0
 */
class VMiddlewareProtectionCsrf extends VMiddleware
{

    /**
     * onBeforeRoute()
     * Checks on request method POST the csrf token, if it doesnt compare set reponsecode to 500
     * 
     * @return void
     */
    function onBeforeRoute()
    {
        $oInput =& VFactory::getInput();
        $oSession =& VFactory::getSession();

        if (strtolower($oInput->getMethod()) == 'post') {
            
            $sNeedToken = $oSession->get('session.csrf_token');
            $sCsrfKey = $oSession->get('session.csrf_key');
            $got_token  = $oInput->get($sCsrfKey, null, 'post');

            if ($got_token != $sNeedToken) {
                
                $sMessage = "Invalid CSRF Token received. Your request is blocked "
                          . "due security reasons. Please go back and try again.";
                // Go to error page
                VResponse::error(
                    500, 
                    $sMessage
                );
            }
        }

    }

    /**
     * onBeforePrepareResponse()
     * Generate new CSRF token, store it to session and assign to template
     * 
     * @return void
     */
    function onBeforePrepareResponse()
    {
        VLoader::import('versions.utilities.password');

        $sCsrfToken = VPassword::create(rand(32, 64));
        $sCsrfKey   = VPassword::create(rand(16, 32));

        $oSession =& VFactory::getSession();
        
        $oSession->set('session.csrf_token', $sCsrfToken);
        $oSession->set('session.csrf_key', $sCsrfKey);

        $sHiddenField = sprintf(
            "<input type='hidden' name='%s' value='%s' />", 
            $sCsrfKey,
            $sCsrfToken
        );
        
        $document =& VFactory::getDocument();
        $document->assign('csrf_token', $sHiddenField);
    }

}
