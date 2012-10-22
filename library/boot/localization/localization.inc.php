<?php

function t() {
  return call_user_func_array("BLocalization::_", func_get_args());
}

class BLocalization extends BObject
{

  /**
   * Available locales
   *
   * @var array Locales
   */
  var $locales = array(
    'en' => 'en_US',
    'de' => 'de_DE'
  );

  var $enabled = array();

  /**
   * The users locale
   *
   * @var string 2 char locale
   */
  var $user_locale = 'en';

  static $_instance = null;

  public function __construct()
 {
    // fetch cookie or default

    $this->set('enabled', BSettings::f('localization.locales', array('foo')));
    $this->set('user_locale', $this->getUserLocale());

  }

  public static function &getInstance($type=null)
  {

    if (!isset(self::$_instance) && !is_object(self::$_instance)) {

      if (is_null($type)) {
        $type = BSettings::f('localization.engine', 'none');
      }

      $classname = sprintf('BLocalization%s', ucfirst($type));

      if (!class_exists($classname))
{
        BLoader::register($classname, dirname(__FILE__).DS.'engines'.DS.(BString::camelcase_to_underscores($type)).'.inc.php');
        BLoader::autoload($classname);

        if (!class_exists($classname))
{
          die( sprintf('Invalid BLocalization type received: %s', $type) );
        }

      }

      self::$_instance = new $classname();
    }

    return self::$_instance;
  }



  /*
   * public function _()
  {
    $self =& self::getInstance();
    return call_user_func_array(array($self, 'translate'), func_get_args());
  }
   */

  public function setLocale($locale)
  {
    if (!$this->isLocale($locale)) return false;
    $this->setUserLocale($locale);

    $this->set('user_locale', $locale);
    return $locale;
  }

  public function getLocale($ietf=false)
  {
    return (($ietf) ? $this->locales[($this->get('user_locale'))] : $this->get('user_locale'));
  }

  public function isLocale($locale)
  {
    return (bool)(isset($this->locales[$locale]) && in_array($locale, $this->enabled));
  }

  public function setUserLocale($locale)
  {
    $cookie = BInput::getInstance('cookie');
    $cookie->set('user_locale', $locale, time()+(60*60*24*365), '/');
  }

  public function getUserLocale()
 {

    $default_lang = null;
    $browser_lang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
    if ($this->isLocale($browser_lang)) {
      $default_lang = $browser_lang;
    }

    if (is_null($default_lang)) {
      $default_lang = BSettings::f('localization.default_locale', 'en');
    }

    $oInput =& BInput::getInstance('cookie');
    return $oInput->get('user_locale', $default_lang, 'cookie');
  }

  public function record()
  {

  }

  public function _($string, $options=array())
  {
    return $string;
  }
}