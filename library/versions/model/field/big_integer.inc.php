<?php

class VModelFieldBigInteger extends VModelFieldInteger 
{
    
    var $type = 'integer';

    var $min_value = -9223372036854775808;
    
    var $max_value = 9223372036854775807;
    
    var $unsigned  = false;
    
    var $zerofill = false;
    
    var $default = 0;
    
    public function __construct($options=array())
    {
        parent::__construct($options);
    }
    
}