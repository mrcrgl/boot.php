<?php

class VMiddlewareBaseLocalization extends VMiddleware {

	public function onBeforeRoute() {
    $localization =& VLocalization::getInstance();
    #die('what the fuck');
	  #printf("Eingang: %s", $_SERVER['REQUEST_URI']);
	  if (preg_match('/^\/([a-z]{2})\/(.{0,255})/', $_SERVER['REQUEST_URI'], $matches)) {

      $locale = $matches[1];
      $_SERVER['REQUEST_URI'] = '/'.$matches[2];
      #printf("REQUEST_URI: %s".NL, $_SERVER['REQUEST_URI']);
      #printf("LOCALE: %s".NL, $locale);

      if ($localization->getLocale() != $locale) {
        if (!$localization->setLocale($locale)) {
          #print "foo?";
          VResponse::redirect(sprintf("/%s%s", $localization->getLocale(), $_SERVER['REQUEST_URI']));
          exit;
        }
      }
	  }

	  else {
	    VResponse::redirect(sprintf("/%s%s", $localization->getLocale(), $_SERVER['REQUEST_URI']));
	    exit;
	  }

	}

	public function onBeforePrepareView() {
	  $localization =& VLocalization::getInstance();
		$document =& VFactory::getDocument();

		$document->setUrlPrefix( sprintf("%s/%s", $localization->getLocale(), (($document->getUrlPrefix() == '/') ? '' : $document->getUrlPrefix())) );
	}

	public function onBeforeQuit() {
	  if (VSettings::f('localization.record', false)) {
  	  $localization =& VLocalization::getInstance();
  	  $localization->record();
	  }
	  #var_dump(apc_fetch($_SERVER['HTTP_HOST']));
	  #die('fi');
	}
}