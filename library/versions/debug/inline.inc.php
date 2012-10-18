<?php

/**
 * Debug Handler "inline"
 * 
 * @package     Versions.core
 * @subpackage  Debug
 * @since       2.0
 */
class VDebugInline implements VDebugInterface 
{
    
    static $collection = array();
    
    static function init()
    {
        
    }
    
    static function report(VDebugMessage $message)
        {
        #var_dump($message);
        array_push(self::$collection, $message);
        
        if (VSettings::f('debug.level') >= $message->level) {
            print $message;
        }
        
    }
    
    static function get()
        {
        
    }
    
    static function get_last()
        {
        
    }
    
}