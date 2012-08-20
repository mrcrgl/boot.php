<?php 


class VModelFieldPassword extends VModelField {
	
	var $type = 'password';
	
	var $min_length = 32;
	
	var $max_length = 32;
	
	public function onSet($value) {
		if (strlen($value) == 32)
			return $value;
		if (strlen($value) >= 5)
			return md5($value);
		
		return $this->_model->get($this->get('field_name'));
	}
	
	public function onUpdate($value) {
		return $value;
	}
}