<?php

class VModelFieldMediumInteger extends VModelFieldInteger {
	
	var $type = 'integer';

	var $min_value = -8388608;
	
	var $max_value = 8388607;
	
	var $unsigned  = false;
	
	var $zerofill = false;
	
	var $default = 0;
	
	public function __construct($options=array()) {
		parent::__construct($options);
	}
	
}