<?php

class VModelFieldBoolean extends VModelField {
	
	var $type = 'boolean';
	
	var $default = false;
	
	public function __construct($options=array()) {
		parent::__construct($options);
	}
	
}