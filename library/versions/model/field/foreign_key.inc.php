<?php

class VModelFieldForeignKey extends VModelFieldChar {
	
	//var $limit_choices_to = null;
	
	//var $related_name = null;
	
	//var $to_field = null;
	
	var $type 				= 'string';
	
	var $min_length 	= 13;
	
	var $max_length 	= 13;
	
	var $foreign_key = true;
	
	var $_related = null;
	
	var $reference = null;
	
	var $reference_table = null;
	
	var $reference_pk = null;
	
	var $validators   = array(
		'hexuid'
	);
	
	public function __construct($options=array()) {
		#print_r($options);
		parent::__construct($options);
		
		#var_dump($options);
		#printf("this should not be empty: %s".NL, $this->get('reference'));
		
		$designer =& VDatabaseDesigner::getInstance();
		$reference_table_name = $designer->getTableName($this->get('reference'));
		$this->set('reference_table', $reference_table_name);
		$this->set('reference_pk', 'uid');
		if ($reference_table_name == $this->get('field_name')) {
			$this->set('db_column', $reference_table_name.'_'.$this->get('reference_pk'));
		} else {
			$this->set('db_column', $this->get('field_name').'_'.$reference_table_name.'_'.$this->get('reference_pk'));
		}
		 
	}
	
	public function onCreate($value) {
		return $this->getUniqueId($value);
	}
	
	public function onUpdate($value) {
		return $this->getUniqueId($value);
	}
	
	public function onSet($value) {
		return $this->getUniqueId($value);
	}
	
	public function onGet($value) {
		return $this->getReferenceObject($value);
	}
	
	public function getUniqueId($mixed) {
		if (is_object($mixed) && $mixed->isValid()) {
			return $mixed->get('uid');
		}
		elseif ( Validator::is($mixed, 'hexuid') ) {
			return $mixed;
		}
		return null;
	}
	
	public function getReferenceObject($mixed) {
		if (is_object($mixed) && $mixed->isValid()) {
			return $mixed;
		}
		elseif ( Validator::is($mixed, 'hexuid') ) {
			$ref = new $this->reference();
			$ref->objects->filter(sprintf('[uid:%s]', $mixed))->get();
			if ($ref->isValid()) return $ref;
		}
		return null;
	}
}