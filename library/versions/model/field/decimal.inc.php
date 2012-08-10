<?php

class VModelFieldDecimal extends VModelField {
	
	var $max_digits = 8;
	
	var $decimal_places = 2;
	
	public function __construct($options=array()) {
		parent::__construct($options);
	}
	
}