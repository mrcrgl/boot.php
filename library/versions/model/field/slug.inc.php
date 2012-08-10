<?php

class VModelFieldSlug extends VModelField {
	
	var $max_length = 50;
	
	public function __construct($options=array()) {
		parent::__construct($options);
	}
	
}