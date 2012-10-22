<?php

class BModelFieldDecimal extends BModelField 
{
    
    var $type = 'float';
    
    var $max_digits = 8;
    
    var $decimal_places = 2;
    
    var $unsigned  = false;
    
    var $default = 0.0;
    
    public function __construct($options=array())
    {
        parent::__construct($options);
    }
    
}