<?php

class BText 
{

  static $localization = null;

  public static function _($string, $options=array())
 {
        // TODO integrate Language System

    if (!self::$localization) {
      self::$localization =& BLocalization::getInstance();
    }

        return self::$localization->_($string, &$options);
    }
}