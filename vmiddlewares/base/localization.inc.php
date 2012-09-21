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

      if ($localization->get('locale') != $locale) {
        if (!$localization->setUserLocale($locale)) {
          #print "foo?";
          VResponse::redirect(sprintf("/%s%s", $localization->get('locale'), $_SERVER['REQUEST_URI']));
          exit;
        }
      }
	  }

	  else {
	    VResponse::redirect(sprintf("/%s%s", $localization->get('locale'), $_SERVER['REQUEST_URI']));
	    exit;
	  }

	}

	public function onBeforePrepareView() {
	  $localization =& VLocalization::getInstance();
		$document =& VFactory::getDocument();

		$document->setUrlPrefix( sprintf("%s/%s", $localization->get('locale'), (($document->getUrlPrefix() == '/') ? '' : $document->getUrlPrefix())) );
	}
}