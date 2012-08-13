<?php

class VModelFieldPrimaryKey extends VModelField {
	
	var $primary_key = true;
	
	var $unique = false;
	
	var $validators = array('hexuid');
	
	public function __construct($options=array()) {
		parent::__construct($options);
	}
	
}