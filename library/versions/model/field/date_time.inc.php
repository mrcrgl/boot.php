<?php

class VModelFieldDateTime extends VModelField {
	
	var $type = 'datetime';
	
	var $auto_now = false;
	
	var $auto_now_add = false;
	
	var $null = true;
	
	var $default = null;
	
	public function __construct($options=array()) {
		parent::__construct($options);
		
	}
	
	public function onUpdate($value) {
		if ($this->get('auto_now') == true) {
			return date("Y-m-d H:i:s");
		}
		return $value;
	}
	
}