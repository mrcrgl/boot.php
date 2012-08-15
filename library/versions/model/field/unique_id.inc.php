<?php

class VModelFieldUniqueId extends VModelFieldPrimaryKey {
	
	var $primary_key 	= true;
	
	var $type 				= 'string';
	
	var $min_length 	= 13;
	
	var $max_length 	= 13;
	
	var $editable   	= false;
	
	var $validators   = array(
		'hexuid'
	);
	
	public function onInitialize($value) {
		return uniqid();
	}
	
}