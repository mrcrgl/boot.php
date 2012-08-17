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
		
		$this->set('related', &$related);
		
		$this->initializeRelation();
	}
	
	
	
	
	private function initializeRelation() {
		
		if ($this->checkRelation(&$this->_model, get_class($this->related))) {
			print "Parent is master and related is related".NL;
			$this->reverse = false;
			#$this->filter('');
		}
		elseif ($this->checkRelation(&$this->related, get_class($this->_model))) {
			print "Parent is related and related is master / reverse mode".NL;
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
			print "Nix von beidem.".NL;
		}
	}
	
	private function checkRelation(&$model, $related) {
		foreach ($model->getFields() as $field) {
			$declaration =& $model->getFieldDeclaration($field);
			
			// strip prefix: VModelField and check for relational fields
			if (!in_array(substr(get_class($declaration), 11), $this->relation_types)) {
				continue;
			}
			print $declaration->get('reference');
			if ($declaration->get('reference') == $related) {
				$this->declaration =& $declaration;
				return true;
			}
		}
		return false;
	}
}