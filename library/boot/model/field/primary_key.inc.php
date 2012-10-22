<?php

class BModelFieldPrimaryKey extends BModelField 
{
    
    var $primary_key = true;
    
    var $unique = false;
    
    var $editable = false;
    
    var $validators = array('hexuid');
    
    public function __construct($options=array())
    {
        parent::__construct($options);
    }
    
}