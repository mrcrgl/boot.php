<?php

class VModelFieldPrimaryKey extends VModelField {
	
	
	
	var $validators = array('hexuid');
	
	public function __construct($options=array()) {
		parent::__construct($options);
	}
	
}