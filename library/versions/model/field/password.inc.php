<?php 


class VModelFieldPassword extends VModelField {
	
	var $min_length = 32;
	
	var $max_length = 32;
	
	public function onSet($value) {
		return md5($value);
	}
}