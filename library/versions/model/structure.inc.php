<?php 

class VModelStructure extends VObject {
	
	#var $_fields = array();
	var $_valid = false;
	
	public function __construct($__uid=null) {
		
		$this->initialize();
		
		if (!is_null($__uid)) $this->load($__uid);
		
	}
	
	public function getModelVersion() {
		return 2;
	}
	
	private function initialize() {
		VModelField::prepareModel(&$this);
		
		foreach ($this->getFields() as $field) {
			$this->set($field, $this->getFieldDeclaration($field)->onInitialize( $this->get($field) ), true);
		}
	}
	
	public function bulkSet($params, $bypasscheck=false) {
		$fields = $this->getFields();
		
		foreach ($params as $k => $v) {
			if (in_array($k, $fields)) {
				$this->set($k, $v, $bypasscheck);
			}
		}
	}
	
	public function save() {
		$designer = VDatabaseDesigner::getInstance();
  	return $designer->saveModel(&$this);
	}
	
	public function load($pk) {
		$designer = VDatabaseDesigner::getInstance();
  	$group = $designer->getModel(&$this, array('pk'=>$pk));
	}
	
	public function set($__field, $__value, $bypasscheck=false) {
		$__value = $this->getFieldDeclaration($__field)->onSet($__value);
		
		if (!$bypasscheck && !$this->checkField($__field, $__value)) {
			return false;
		}
		
		parent::set($__field, $__value);
		
		return true;
	}
	
	public function get($__field) {
		$__value = parent::get($__field);
		if ($this->getFieldDeclaration($__field))
			return $this->getFieldDeclaration($__field)->onGet($__value);
		return null;
	}
	
	public function isValid($set=null) {
		if (!is_null($set)) {
			$this->_valid = (bool)$set;
		}
		return $this->_valid;
	}
	
	public function __set($var, $value) {
		// TODO var does not exist
	}
	
	public function __get($__var) {
		// TODO var does not exist
	}
	
	public function getFields() {
		$class_vars = get_class_vars(get_class(&$this));
		$fields     = array();
		foreach ($class_vars as $column => $declaration) {
			if (preg_match('/^_/', $column)) continue;
			$fields[] = $column;
		}
		return $fields;
	}
	
	public function getFieldDeclaration($__field) {
		return VModelField::getInstance(get_class(&$this), $__field);
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
