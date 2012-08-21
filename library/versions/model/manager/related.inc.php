<?php

class VModelManagerRelated extends VModelManager {
	
	var $related = null;
	
	var $reverse = null;
	
	var $declaration = null;
	
	var $relation_types = array(
		'ForeignKey',
		'ManyToMany',
		'OneToOne'
	);
	
	public function __construct(&$model, &$related) {
		parent::__construct(&$model);
		#var_dump($model);
		$this->set('related', &$related);
		
		$this->initializeRelation();
	}
	
	public function clear() {
		$dbo =& VDatabase::getInstance();
		$dbo->userQuery(sprintf(
			"DELETE FROM `%s` WHERE `%s` = '%s'",
			$this->declaration->get('reference_table'),
			$this->declaration->get('model_pk'),
			$this->declaration->get('_model')->get('uid')
		));
	}
	
	public function bulkAdd($array, $truncate=true) {
		if ($truncate) {
			$this->clear();
		}
		
		foreach ($array as $value) {
			if (Validator::is($value, 'hexuid') || is_object($value))
				$this->add($value);
		}
	}
	
	public function add($mixed) {
		$mixed = ((is_object($mixed)) ? $mixed->get('uid') : $mixed);
		
		$dbo =& VDatabase::getInstance();
		$dbo->userQuery(sprintf(
			"INSERT IGNORE INTO `%s` SET `%s` = '%s', `%s` = '%s'",
			$this->declaration->get('reference_table'),
			$this->declaration->get('model_pk'),
			$this->declaration->get('_model')->get('uid'),
			$this->declaration->get('reference_pk'),
			$mixed
		));
	}
	
	public function has($mixed) {
		$mixed = ((is_object($mixed)) ? $mixed->get('uid') : $mixed);
		
		$dbo =& VDatabase::getInstance();
		$dbo->userQuery(sprintf(
			"SELECT * FROM `%s` WHERE `%s` = '%s' AND `%s` = '%s'",
			$this->declaration->get('reference_table'),
			$this->declaration->get('model_pk'),
			$this->declaration->get('_model')->get('uid'),
			$this->declaration->get('reference_pk'),
			$mixed
		));
		
		return (bool)$dbo->getNumRows();
	}
	
	private function initializeRelation() {
		
		if ($this->checkRelation(&$this->_model, get_class($this->related))) {
			// TODO: set debug message
			#print "Parent is master and related is related".NL;
			$this->reverse = false;
			#$this->filter('');
		}
		elseif ($this->checkRelation(&$this->related, get_class($this->_model))) {
			// TODO: set debug message
			#print "Parent is related and related is master / reverse mode".NL;
			$this->reverse = true;
			
			// TODO: set the group_uid at second parameter
			#print sprintf('[%s:%s]', $this->declaration->get('db_column'), 'theuid');
			$this->filter( sprintf('[%s:%s]', $this->declaration->get('field_name'), $this->_model->get('uid')) );
			parent::__construct(&$this->related);
			
			
			// Only for ForeignKeys
			#$designer =& VDatabaseDesigner::getInstance();
			#$this->setTable($designer->getTableName(get_class($this->_model)));
		}
		else {
			// TODO: set debug message
			#print "Nix von beidem.".NL;
		}
	}
	
	private function checkRelation(&$model, $related) {
		foreach ($model->getFields() as $field) {
			$declaration =& $model->getFieldDeclaration($field);
			
			// strip prefix: VModelField and check for relational fields
			if (!in_array(substr(get_class($declaration), 11), $this->relation_types)) {
				continue;
			}
			#print $declaration->get('reference');
			if ($declaration->get('reference') == $related) {
				$this->declaration =& $declaration;
				return true;
			}
		}
		return false;
	}
}