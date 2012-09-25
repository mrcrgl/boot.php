<?php

class VModelStructure extends VObject {

	#var $_fields = array();
	var $_valid = false;

	var $_manager = null;

	var $_allow_cache = true;

	var $_related_manager = array();

	public function __construct($__uid=null) {

		$this->initialize();

		if (!is_null($__uid)) $this->load($__uid);

	}

	public function getModelVersion() {
		return 2;
	}

	private function initialize() {
		#printf("Class initialize: %s + Valid: %s".NL, get_class($this), (($this->isValid()) ? 'true' : 'false'));
		VModelField::prepareModel(&$this);

		foreach ($this->getFields() as $field) {
		  #print "Init field: $field".NL;
			$this->set($field, $this->getFieldDeclaration($field)->onInitialize( $this->get($field) ), true);
		}
	}

	public function bulkSet($params, $bypasscheck=false) {
		$fields = $this->getFields();

		foreach ($fields as $field) {
			$declaration =& $this->getFieldDeclaration($field);
			if (isset($params[($declaration->get('db_column'))])) {
				$params[$field] = $params[($declaration->get('db_column'))];
			}
		}
		#print_r($params);
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
	  $this->objects->get(sprintf('[filter:[uid:%s]]', $pk));
	}

	public function set($__field, $__value, $bypasscheck=false) {
		#print "setting field: ".$__field.' with '.$__value.NL;
		$__value = $this->getFieldDeclaration($__field)->onSet($__value);

		#if (!$bypasscheck && !$this->getFieldDeclaration($__field)->get('editable')) {
			#printf("Field '%s' is not editable.".NL, $__field);
			#return false;
		#}

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
		if ($__var == 'objects') {
			if (is_null($this->_manager))
				$this->_manager = VModelManager::getInstance(&$this);
			return $this->_manager;
		}
		if (substr($__var, -5, 5) == '__set') {
		  #print $__var.NL;exit();
		  if (!$this->_allow_cache || !isset($this->_related_manager[$__var])) {
		    $reference = (($this->getFieldDeclaration(substr($__var, 0, -5))) ? $this->getFieldDeclaration(substr($__var, 0, -5))->get('reference') : null );
		    if ($reference) {
		      $related = new $reference();
		    } else {
		      $related_name = VString::underscores_to_camelcase(substr($__var, 0, -5));
		      $related = new $related_name();
		    }
		    $this->_related_manager[$__var] = VModelManager::getInstance(&$this, $related, 'related');
		  }
		  #else { print "den jibbet schon"; }
      #var_dump($this->_related_manager[$__var]);
      #exit;
			return $this->_related_manager[$__var];
		}


		// TODO var does not exist
	}

	private function getRelationalManager($__related_to) {

	}

	public function getFields($only_db_columns=false) {
		$class_vars = get_class_vars(get_class(&$this));
		$fields     = array();
		foreach ($class_vars as $column => $declaration) {
			if (preg_match('/^_/', $column)) continue;
			if ($only_db_columns && $this->getFieldDeclaration($column)->get('type') == 'none') continue;
			$fields[] = $column;
		}
		return $fields;
	}

	public function getFieldDeclaration($__field) {
		#print 'getFieldDeclaration said: '.$__field.NL;
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
