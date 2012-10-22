<?php

class BModelFieldSmallInteger extends BModelFieldInteger 
{
    
    var $type = 'integer';
    
    var $min_value = -32768;
    
    var $max_value = 32767;
    
    var $unsigned  = false;
    
    var $zerofill = false;
    
    var $default = 0;
    
    public function __construct($options=array())
    {
        parent::__construct($options);
    }
    
}