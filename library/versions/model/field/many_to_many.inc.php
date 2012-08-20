<?php 


class VModelFieldManyToMany extends VModelField {
	
	var $type 				= 'none';
	
	#var $foreign_key = true;
	
	var $many_to_many = true;
	
	var $_related = null;
	
	var $reference = null;
	
	var $reference_table = null;
	
	var $reference_pk = null;
	
	var $db_reference_column = null;
	
	var $model_pk = null;
	
	var $validators   = array(
		'hexuid'
	);
	
	public function __construct($options=array()) {
		#print_r($options);
		parent::__construct($options);
		
		#var_dump($options);
		#printf("this should not be empty: %s".NL, $this->get('reference'));
		
		$designer =& VDatabaseDesigner::getInstance();
		
		if ($this->get('reference_table') == null) {
			// create table definition
			$reference_table_name  = $designer->getTableName($this->get('_model'));
			$reference_table_name .= '_to_';
			$reference_table_name .= $designer->getTableName($this->get('reference'));
			$this->set('reference_table', $reference_table_name);
		}
		
		// TODO: hier muss test_user_uid und test_tag_uid herauskommen...
		$this->set('model_pk', $designer->getTableName($this->get('_model')).'_uid');
		$this->set('reference_pk', $designer->getTableName($this->get('reference')).'_uid');
		
		$this->set('db_column', $designer->getTableName($this->get('_model')).'_'.$this->get('reference_pk'));
		$this->set('db_reference_column', $designer->getTableName($this->get('reference')).'_'.$this->get('reference_pk'));
		 
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