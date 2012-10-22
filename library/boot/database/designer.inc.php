<?php

BLoader::discover(dirname(__FILE__).DS.'designer');

abstract class BDatabaseDesigner 
{
  
  static public function getInstance($type='mysql')
 {
    
    $classname = 'BDatabaseDesigner'.ucfirst($type);
    
    BLoader::autoload($classname);
    if (!class_exists($classname)) 
{
      throw new Exception( sprintf('Database designer %s not found. Exiting...', $classname) );
      //user_error()
    }
    
    return new $classname();
  }
  
}