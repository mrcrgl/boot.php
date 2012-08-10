<?php 


class VModelFieldEmail extends VModelField {
	
	var $max_length = 75;
	
	var $validators = array('email');
	
	public function __construct($options=array()) {
		parent::__construct($options);
	}
	
}