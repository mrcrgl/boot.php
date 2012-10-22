<?php
/*
 * @build   27.01.2009
 * @project DMTp
 * @package Instance
 *
 * @author  Marc Riegel
 * @contact mr@riegel.it
 *
 * --
 *
 * --
 */
class BInstance 
{
  static $arrInstances = array();

  public final function __construct()
 { }

  public final function __clone()
 { }

  static final function loadPersistent()
 {
    if (isset($_SESSION[(BSettings::f('default.secret', uniqid()))]['Instance']) && Validator::is($_SESSION[(BSettings::f('default.secret', uniqid()))]['Instance'], 'array')) {
      foreach ($_SESSION[(BSettings::f('default.secret', uniqid()))]['Instance'] as $ident => $reference) {
        self::_new($reference, $ident);
        # TODO
        #ENV::$parse[('_'.strtolower($ident))] = $reference;
      }
    }
  }

  static final function f($__memberName)
      {
    if (isset(self::$arrInstances[$__memberName])) {
      return self::$arrInstances[$__memberName];
    }
    return false;
  }

  static final function _new($reference, $ident=false, $persistent=false)
  {
    if (!$ident) {
      $ident = get_class($reference);
    }
    if (!$ident) {
      return false;
    }
    self::$arrInstances[$ident] = &$reference;
    if ($persistent) {
      $_SESSION[(BSettings::f('default.secret', uniqid()))]['Instance'][$ident] = &$reference;
    }
  }

  static final function _unset($ident)
    {
    if (isset($_SESSION[(BSettings::f('default.secret', uniqid()))]['Instance'][$ident])) {
      unset($_SESSION[(BSettings::f('default.secret', uniqid()))]['Instance'][$ident]);
    }
    unset (self::$arrInstances[$ident]);
  }

  static final function _unsetNonPersistent()
  {
    foreach (self::$arrInstances as $ident => $reference) {
      if (!isset($_SESSION[(BSettings::f('default.secret', uniqid()))]['Instance'][$ident])) {
        unset (self::$arrInstances[$ident]);
      }
    }
  }
}
?>
