<?php

class VModelFieldForeignKey extends VModelField {
	
	//var $limit_choices_to = null;
	
	//var $related_name = null;
	
	//var $to_field = null;
	
	var $reference = null;
	
	
	public function __construct($options=array()) {
		parent::__construct($options);
	}
	
}