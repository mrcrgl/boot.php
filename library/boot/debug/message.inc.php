<?php

define('DEBUG_ERROR',         1);
define('DEBUG_EXCEPTION', 2);
define('DEBUG_WARNING',     3);
define('DEBUG_NOTICE',         4);
define('DEBUG_INFO',             5);
define('DEBUG_MESSSAGE',     6);

#BLoader::register('BDirectory', VLIB.DS.'boot'.DS.'utilities'.DS.'directory.inc.php');

/**
 * Debug Message
 *
 * @package     boot.php.core
 * @subpackage  Debug
 * @since       2.0
 */
class BDebugMessage
{

  /**
   *
   * @var bool
   */
  var $valid;

    /**
     *
     * Enter description here ...
     * @var unknown_type
     */
    var $message;

    /**
     *
     * Enter description here ...
     * @var unknown_type
     */
    var $level;

    /**
     *
     * Enter description here ...
     * @var unknown_type
     */
    var $file;

    /**
     *
     * Enter description here ...
     * @var unknown_type
     */
    var $line;

    /**
     *
     * collection of php error-codes with local definitions
     * source: http://www.php.net/manual/en/errorfunc.constants.php
     *
     * @var array php_error_codes
     */
    private $php_error_codes = array(
        1                => array(DEBUG_ERROR,     'E_ERROR'),
        2                => array(DEBUG_WARNING, 'E_WARNING'),
        4                => array(DEBUG_ERROR,     'E_PARSE'),
        8                => array(DEBUG_NOTICE,     'E_NOTICE'),
        16            => array(DEBUG_ERROR,     'E_CORE_ERROR'),
        32            => array(DEBUG_ERROR,     'E_CORE_WARNING'),
        64            => array(DEBUG_ERROR,     'E_COMPILE_ERROR'),
        128            => array(DEBUG_WARNING, 'E_COMPILE_WARNING'),
        256            => array(DEBUG_ERROR,     'E_USER_ERROR'),
        512            => array(DEBUG_WARNING, 'E_USER_WARNING'),
        1024        => array(DEBUG_NOTICE,     'E_USER_NOTICE'),
        2048        => array(DEBUG_WARNING, 'E_STRICT'),
        4096        => array(DEBUG_ERROR,     'E_RECOVERABLE_ERROR'),
        8192        => array(DEBUG_NOTICE,     'E_DEPRECATED'),
        16384        => array(DEBUG_NOTICE,     'E_USER_DEPRECATED'),
        32767        => array(DEBUG_INFO,         'E_ALL')
    );

    /**
     *
     * contructor of BDebugMessage
     *
     * @param string $message
     * @param integer $level
     * @param integer $tracelevel
     * @param string $file
     * @param integer $line
     */
    function __construct($message, $level=6, $tracelevel=null, $file=null, $line=null)
    {

      #print "Debug Level: ".BSettings::f('debug.level').NL;

      if (!defined('DEBUG_LEVEL') && BSettings::$initialized) {
        define('DEBUG_LEVEL',     BSettings::f('debug.level'));
      }

      if (defined('DEBUG_LEVEL') && (DEBUG_LEVEL === false || $level >= DEBUG_LEVEL)) {
        #print "DEBUG_LEVEL ".DEBUG_LEVEL." to low".NL;
        $this->valid = false;
        return false;
      }



        /*
         * Array
         * (
     *  [type] => 8
     *  [message] => Undefined variable: a
     *  [file] => C:\WWW\index.php
     *  [line] => 2
     * )
         *
         */
        if (is_null($file) && is_null($line)) {
            $backtrace = debug_backtrace();

            $lv = ((!is_null($tracelevel) ? 0+$tracelevel : 0));

            $file = $backtrace[$lv]['file'];
            $line = $backtrace[$lv]['line'];
        }

        if (is_null($level) && isset($code) && isset($this->php_error_codes[$code])) {
            $level = 3; //$this->php_error_codes[$code][0];
        }

        $this->message     = $message;
        $this->level        = $level;
        $this->file         = (string)new BDirectory($file);
        $this->line         = $line;
        $this->valid    = true;
    }

    function __toString()
    {
        return sprintf("[%d] (%s:%d): %s".NL, $this->level, $this->file, $this->line, $this->message);
    }

}