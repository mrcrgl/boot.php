<?php

class BModelFieldInteger extends BModelField 
{
    
    var $type = 'integer';
    
    var $min_value = -2147483648;
    
    var $max_value = 2147483647;
    
    var $unsigned  = false;
    
    var $zerofill = false;
    
    var $default = 0;
    
    public function __construct($options=array())
    {
        
        if (isset($options['unsigned']) && $options['unsigned'] == true) {
            $this->set('min_value', 0);
            $this->set('max_value', (int)$this->get('max_value')*2);
        }
        
        parent::__construct($options);
    }
    
}