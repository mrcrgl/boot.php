<?php

class VLocalizationGettext extends VLocalization 
{

  public function __construct()
 {
    parent::__construct();

    putenv(sprintf('LC_ALL=%s', $this->getLocale(true)));
    setlocale(LC_ALL, sprintf('%s.UTF-8', $this->getLocale(true)));

    // Angeben des Pfads der Übersetzungstabellen
    bindtextdomain("lang", PROJECT_CONFIG.DS.'translation'.DS);
    bind_textdomain_codeset("myAppPhp", 'UTF-8');

    // Domain auswählen
    textdomain("lang");
  }

  public function translate()
  {
    $args = func_get_args();

    if (!isset($args[0]) || !$args[0]) return false;

    $args[0] = gettext($args[0]);

    return call_user_func_array('sprintf', $args);
  }

}