<?php

class VModelFieldTime extends VModelField {
	
	var $type = 'time';
	
	public function __construct($options=array()) {
		parent::__construct($options);
	}
	
}