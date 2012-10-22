<?php

class BModelFieldDate extends BModelField 
{
    
    var $type = 'date';
    
    var $auto_now = false;
    
    var $auto_now_add = false;
    
    public function __construct($options=array())
    {
        parent::__construct($options);
    }
    
}