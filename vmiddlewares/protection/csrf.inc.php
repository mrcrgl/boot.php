<?php
/**
 * Enable Middleware: CSRF.
 *
 * Load this Middleware to enable CSRF Protection
 *
 * @author  Marc Riegel
 * @package boot.php.Middleware
 * @version 1.0
 */

/**
 * Enable Middleware: CSRF.
 *
 * @name    BMiddlewareProtectionCsrf
 * @package boot.php.Middleware
 * @see     BMiddleware
 */
class BMiddlewareProtectionCsrf extends BMiddleware
{

    /**
     * Event fired before Route.
     *
     * Checks on request method POST the csrf token,
     * if it doesnt compare set reponsecode to 500
     *
     * @return void
     */
    function onBeforeRoute()
     {
        $oInput =& BFactory::getInput();
        $oSession =& BFactory::getSession();

        if (strtolower($oInput->getMethod()) == 'post') {
            
            $sNeedToken = $oSession->get('session.csrf_token');
            $sCsrfKey = $oSession->get('session.csrf_key');
            $sGotToken  = $oInput->get($sCsrfKey, null, 'post');

            if ($sGotToken != $sNeedToken) {
                
                $sMessage = "Invalid CSRF Token received. Your request "
                          . "is blocked due security reasons. Please go "
                          . "back and try again.";
                // Go to error page.
                BResponse::error(
                    500,
                    $sMessage
                );
            }
            
        }

    }

    /**
     * Event fired before preparing Response.
     *
     * Generate new CSRF token, store it to session and assign to template
     *
     * @return void
     */
    function onBeforePrepareResponse()
     {
        BLoader::import('boot.utilities.password');

        $sCsrfToken = BPassword::create(rand(32, 64));
        $sCsrfKey   = BPassword::create(rand(16, 32));

        $oSession =& BFactory::getSession();
        
        $oSession->set('session.csrf_token', $sCsrfToken);
        $oSession->set('session.csrf_key', $sCsrfKey);

        $sHiddenField = sprintf(
            "<input type='hidden' name='%s' value='%s' />",
            $sCsrfKey,
            $sCsrfToken
        );
        
        $oDocument =& BFactory::getDocument();
        $oDocument->assign('csrf_token', $sHiddenField);
    }

}
