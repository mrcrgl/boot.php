<?php

class VModelFieldBoolean extends VModelField 
{

    var $type = 'boolean';

    var $default = 1;

    var $null = false;

    public function __construct($options=array())
   {
        parent::__construct($options);
    }

}