<?php

class BModelFieldTime extends BModelField 
{
    
    var $type = 'time';
    
    public function __construct($options=array())
    {
        parent::__construct($options);
    }
    
}