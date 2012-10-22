<?php

class BModelFieldText extends BModelField 
{
    
    var $type = 'string';

    var $max_length = 65535;
    
    public function __construct($options=array())
    {
        parent::__construct($options);
    }
    
}