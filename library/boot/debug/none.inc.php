<?php

/**
 * Debug Handler "none"
 *
 * @package     boot.php.core
 * @subpackage  Debug
 * @since       2.0
 */
class BDebugNone implements BDebugInterface 
{
    
    static $collection = array();
    
    static function init()
    {
        
    }
    
    static function report(BDebugMessage $message)
        {
        array_push(self::$collection, $message);
    }
    
    static function get()
    {
        
    }
    
    static function get_last()
        {
        
    }
    
}