<?php

BLoader::discover(dirname(__FILE__).DS.'adapter');

abstract class BDatabase 
{
    
    static public function getInstance($type='mysql', $host=null, $database=null, $user=null, $pass=null)
 {
        
        $classname = 'BDatabase'.ucfirst($type);
        
        if (!class_exists($classname)) 
{
            throw new Exception( sprintf('Database adapter %s not found. Exiting...', $classname) );
            //user_error()
        }
        
        return new $classname($host, $database, $user, $pass);
    }
    
}