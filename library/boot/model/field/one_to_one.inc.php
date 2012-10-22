<?php 


class BModelFieldOneToOne extends BModelField 
{
    
    var $type = 'string';
    
    var $min_length = 13;
    
    var $max_length = 13;
    
    public function __construct($options)
    {
        
        $options['db_column'] = $options['db_column'].'_uid';
        
        parent::__construct($options);
    }
    
}