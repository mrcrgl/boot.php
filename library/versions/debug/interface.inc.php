<?php

/**
 * The Debug Interface
 *
 * @package     Versions.core
 * @subpackage  Base
 * @since       2.0
 */
interface VDebugInterface {
    
    static function init();
    
    static function report(VDebugMessage $message);
    
    static function get();
    
    static function get_last();
    
}