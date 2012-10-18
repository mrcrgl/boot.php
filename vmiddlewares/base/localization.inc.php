<?php


/**
 * Enable Middleware: Localization.
 * 
 * @desc      Load this Middleware to enable Localization
 *            and prepare URLs with language tags
 * 
 * @author    mriegel
 * @package   Versions.Middleware
 * @version   1.0
 */
class VMiddlewareBaseLocalization extends VMiddleware
{

    /**
     * onBeforeRoute()
     * checks for localization tag in url, redirect 
     * with default if its not available
     * 
     * @return void
     */
    public function onBeforeRoute()
     {
        $localization =& VLocalization::getInstance();
        
        if (preg_match(
            '/^\/([a-z]{2})\/(.{0,255})/', 
            $_SERVER['REQUEST_URI'], 
            $matches
        )) {

            $locale = $matches[1];
            $_SERVER['REQUEST_URI'] = '/'.$matches[2];

            if ($localization->getLocale() != $locale) {
                if (!$localization->setLocale($locale)) {
                    $sRedirectTo = sprintf(
                        "/%s%s",
                        $localization->getLocale(),
                        $_SERVER['REQUEST_URI']
                    );
                    VResponse::redirect($sRedirectTo);
                    exit;
                }
            }
        } else {
            $sRedirectTo = sprintf(
                "/%s%s",
                $localization->getLocale(),
                $_SERVER['REQUEST_URI']
            );
            VResponse::redirect($sRedirectTo);
            exit;
        }

    }

    /**
     * onBeforePrepareView()
     * Add the localization tag to UrlPrefix
     * 
     * @return void
     */
    public function onBeforePrepareView()
     {
        $localization =& VLocalization::getInstance();
        $oDocument =& VFactory::getDocument();

        $sUrlPrefix = sprintf(
            "%s/%s",
            $localization->getLocale(),
            ($oDocument->getUrlPrefix() == '/') ? '' : $oDocument->getUrlPrefix()
        );
        $oDocument->setUrlPrefix($sUrlPrefix);
    }

    /**
     * onBeforeQuit()
     * For development uses: Fetches all Text Strings 
     * and store it to the translation storage
     * 
     * @return void
     */
    public function onBeforeQuit()
     {
        if (VSettings::f('localization.record', false)) {
            $localization =& VLocalization::getInstance();
            $localization->record();
        }
    }
}
