<?php

/**
 * The Debug Interface
 *
 * @package     boot.php.core
 * @subpackage  Base
 * @since       2.0
 */
interface BDebugInterface {
    
    static function init();
    
    static function report(BDebugMessage $message);
    
    static function get();
    
    static function get_last();
    
}