<?php

class BModelFieldTinyInteger extends BModelFieldInteger 
{
    
    var $type = 'integer';
    
    var $min_value = -128;
    
    var $max_value = 127;
    
    var $unsigned  = false;
    
    var $zerofill = false;
    
    var $default = 0;
    
    public function __construct($options=array())
    {
        parent::__construct($options);
    }
    
}