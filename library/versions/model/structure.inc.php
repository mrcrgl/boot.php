<?php 

class VModelStructure extends VObject {
	
	#var $_fields = array();
	
	public function __construct($__uid) {
		
		$this->initialize();
		
	}
	
	public function getModelVersion() {
		return 2;
	}
	
	private function initialize() {
		VModelField::prepareModel(&$this);
		#print "<pre>";
    #var_dump(VModelField::$_instances);
    #print "</pre>";
	}
	
	public function bulkUpdate($params) {
		
	}
	
	public function save() {
		
	}
	
	public function set($__field, $__value, $bypasscheck=false) {
		if (!$bypasscheck && !$this->checkField($__field, $__value)) {
			return false;
		}
		
		parent::set($__field, $__value);
		
		return true;
	}
	
	public function __set($var, $value) {
		// TODO var does not exist
	}
	
	public function __get($__var) {
		// TODO var does not exist
	}
	
	public function getFields() {
		$class_vars = get_class_vars(get_class($this));
		$fields     = array();
		foreach ($class_vars as $column => $declaration) {
			if (preg_match('/^_/', $column)) continue;
			$fields[] = $column;
		}
		return $fields;
	}
	
	public function getFieldDeclaration($__field) {
		return VModelField::getInstance(get_class($this), $__field);
	}
	
	public function checkFields() {
		foreach ($this->getFields() as $field) {
			if (!$this->checkField($field, $this->get($field))) {
				print "Invalid field: $field".NL;
				return false;
			}
		}
		return true;
	}
	
	public function checkField($__field, $__value) {
		return Validator::checkField($this->getFieldDeclaration($__field), $__value);
	}
}
