<?php

class VText 
{

  static $localization = null;

  public static function _($string, $options=array())
 {
        // TODO integrate Language System

    if (!self::$localization) {
      self::$localization =& VLocalization::getInstance();
    }

        return self::$localization->_($string, &$options);
    }
}