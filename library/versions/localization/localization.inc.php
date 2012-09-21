<?php

function t() {
  return call_user_func_array("VLocalization::_", func_get_args());
}

class VLocalization extends VObject {

  var $locales = array(
    'en' => 'en_US',
    'de' => 'de_DE'
  );

  var $locale = null;

  static $_instance = null;

  public function __construct() {
    // fetch cookie or default

    $this->set('locale', $this->getUserLocale());

  }

  public static function &getInstance($type=null) {

    if (!isset(self::$_instance) && !is_object(self::$_instance)) {

      if (is_null($type)) {
        $type = VSettings::f('localization.engine', 'gettext');
      }

      $classname = sprintf('VLocalization%s', ucfirst($type));

      if (!class_exists($classname)) {
        VLoader::register($classname, dirname(__FILE__).DS.'engines'.DS.(VString::camelcase_to_underscores($type)).'.inc.php');
        VLoader::autoload($classname);

        if (!class_exists($classname)) {
          die( sprintf('Invalid VLocalization type received: %s', $type) );
        }

      }

      self::$_instance = new $classname();
    }

    return self::$_instance;
  }



  public static function _() {
    $self =& self::getInstance();
    return call_user_func_array(array($self, 'translate'), func_get_args());
  }

  public function setLocale($locale) {
    if (!$this->isLocale($locale)) return false;

    $this->setUserLocale($locale);

    $this->set('locale', $locale);
    return $locale;
  }

  public function getLocale($ietf=false) {
    return (($ietf) ? $this->locales[($this->get('locale'))] : $this->get('locale'));
  }

  public function isLocale($locale) {
    return (bool)isset($this->locale[$locale]);
  }

  public function setUserLocale($locale) {
    $cookie = VInput::getInstance('cookie');
    $cookie->set('locale', $locale, (60*60*24*365));
  }

  public function getUserLocale() {
    $input =& VInput::getInstance('cookie');
    return $input->get('locale', VSettings::f('localization.default', 'en'), 'cookie');
  }
}