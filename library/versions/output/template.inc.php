<?php


VLoader::discover(dirname(__FILE__).DS.'template');

abstract class VTemplate 
{
    
    /**
     * @var $template Template engine 
     */
    public static $template = null;
    
    public static function getInstance($type=null)
    {
        
        $classname = 'VTemplate'.ucfirst($type);
        
        if (!class_exists($classname)) 
{
            throw new Exception( sprintf('Template engine %s not found. Exiting...', $classname) );
            //user_error()
        }
        
        return new $classname();
    }
    
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->template, $method), $args);
    }
    
    static public function __callStatic($method, $args)
    {
        throw new Exception('VTemplate: __callStatic not implemented yet.');
    }
}