<?php

VLoader::discover(dirname(__FILE__).DS.'designer');

abstract class VDatabaseDesigner 
{
  
  static public function getInstance($type='mysql')
 {
    
    $classname = 'VDatabaseDesigner'.ucfirst($type);
    
    VLoader::autoload($classname);
    if (!class_exists($classname)) 
{
      throw new Exception( sprintf('Database designer %s not found. Exiting...', $classname) );
      //user_error()
    }
    
    return new $classname();
  }
  
}