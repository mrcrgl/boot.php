<?php

/**
 * Debug Handler "inline"
 * 
 * @package     boot.php.core
 * @subpackage  Debug
 * @since       2.0
 */
class BDebugInline implements BDebugInterface 
{
    
    static $collection = array();
    
    static function init()
    {
        
    }
    
    static function report(BDebugMessage $message)
        {
        #var_dump($message);
        array_push(self::$collection, $message);
        
        if (BSettings::f('debug.level') >= $message->level) {
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