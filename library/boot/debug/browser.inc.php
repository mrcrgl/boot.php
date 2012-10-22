<?php

/**
 * Debug Handler "browser"
 *
 * @package     boot.php.core
 * @subpackage  Base
 * @since       2.0
 */
class BDebugBrowser implements BDebugInterface 
{
    
    static $collection = array();
    
    static function init()
    {
        
    }
    
    static function report(BDebugMessage $message)
        {
        #var_dump($message);
        print $message;
    }
    
    static function get()
    {
        
    }
    
    
    static function get_last()
        {
        
    }
    
}