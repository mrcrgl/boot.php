<?php

VLoader::import('versions.utilities.string');

class ComponentCrudViewModel extends VApplicationView {
	
	var $object_name = null;
	
	var $object_uid = null;
	
	var $object_manager_name = null;
	
	var $object = null;
	
	var $object_manager = null;
	
	var $object_layout_version = null;
	
	public function create() {
		$this->fetchObject();
		
		$document =& VFactory::getDocument();
		$renderer =& $document->getRenderer();
		$renderer->appendTemplateDirPart( $this->getAlternateTemplatePath() );
		
		$document->setTemplate('crud/create.htpl');
		
		if ($renderer->templateExists($this->getAlternateTemplate('create.htpl'))) {
			$document->assign('user_defined_template', $this->getAlternateTemplate('create.htpl'));
		}
  	
  	$input =& VFactory::getInput();
  	if (strtolower($input->getMethod()) == 'post') {
  		$this->updateObject();
  		header( sprintf("Location: /%s%s", $document->getUrlPrefix(), $this->object->uid) );
  		exit;
  	}
  	
	}
	
	public function read() {
		$this->fetchObject();
		
		$document =& VFactory::getDocument();
  	$renderer =& $document->getRenderer();
		$renderer->appendTemplateDirPart( $this->getAlternateTemplatePath() );
		
		$document->setTemplate('crud/read.htpl');
		
		if ($renderer->templateExists($this->getAlternateTemplate('read.htpl'))) {
			$document->assign('user_defined_template', $this->getAlternateTemplate('read.htpl'));
		}
  	
	}
	
	public function update() {
		$this->fetchObject();
		
		$document =& VFactory::getDocument();
  	$renderer =& $document->getRenderer();
		$renderer->appendTemplateDirPart( $this->getAlternateTemplatePath() );
		
		$document->setTemplate('crud/create.htpl');
  	
		if ($renderer->templateExists($this->getAlternateTemplate('create.htpl'))) {
			$document->assign('user_defined_template', $this->getAlternateTemplate('create.htpl'));
		}
		
  	$document->assign('delete_url', sprintf("/%s%s/delete", $document->getUrlPrefix(), $this->object->uid));
  	
		$input =& VFactory::getInput();
  	if (strtolower($input->getMethod()) == 'post') {
  		$this->updateObject();
  	}
	}
	
	public function delete() {
		$this->fetchObject();
		
		$document =& VFactory::getDocument();
		
		$this->object->delete();
		
		VMessages::_('Ok', 'L&ouml;schung erfolgreich!', 'success');
  	
		header( sprintf("Location: /%s", $document->getUrlPrefix()) );
  	exit;
	}
	
	private function fetchObject() {
		
  	$input =& VFactory::getInput();
  	
  	$parent = $input->get('parent', false, 'get');
  	
  	$this->object_name = $input->get('object_name', null, 'get');
  	$this->object_uid = $input->get('object_uid', false, 'get');
  	
  	
  	if (!$this->object_name) {
  		throw new Exception( "Input object_name must be set!" );
  	}
  	
  	$this->object = new $this->object_name($this->object_uid);
  	$this->object_layout_version = $this->object->getModelVersion();
  	
  	if ($this->object_layout_version == 1) {
  		
  		$this->object_manager_name = $this->object_name.'Manager';
  		$this->object_manager = new $this->object_manager_name();
  		$this->object_manager->set('ignore_object_state', true);
  	
  	} else {
  		#print "before init jacket";
  		$this->object_manager = new VModelManagerJacket(&$this->object);
  		#print "after init jacket";
  	}
  	
  	$document =& VFactory::getDocument();
  	
  	if ($parent) {
  		$this->object_manager->filter($parent, $input->get($parent, null, 'get'));
  		$document->assign($parent, $input->get($parent, null, 'get'));
  	}
  	
  	
  	$document->assign('object', &$this->object);
  	$document->assign('manager', &$this->object_manager);
  	
	}
	
	private function getAlternateTemplate($__method) {
		$path = strtolower(implode(DS, VString::explode_camelcase($this->object_name)));
  	
		if (strpos($path, '/model/') !== false) {
			$newpath = 'crud'.DS.substr($path, strpos($path, '/model/')+strlen('/model/')).DS.$__method;
		} else {
			$newpath = 'crud'.DS.$path.DS.$__method;
		}
  	
  	return $newpath;
	}
	
	private function getAlternateTemplatePath() {
		$path = strtolower(implode(DS, VString::explode_camelcase($this->object_name)));
  	
  	$newpath = 'crud'.DS.substr($path, strpos($path, '/model/')+strlen('/model/'));
  	return $newpath;
	}
	
	private function updateObject() {
		$input =& VFactory::getInput();
		
		$params = array();
		foreach ($_POST as $key => $value) {
			$params[$key] = $input->get($key, null, 'post');
		}
		#var_dump($params);
		if ($this->object_layout_version == 1) {
			$bok = $this->object->update($params);
		} elseif ($this->object_layout_version == 2) {
			$this->object->bulkSet($params);
			$bok = $this->object->save();
			
			/*
			 * for ManyToMany relationships
			 */
			foreach ($this->object->getFields() as $field) {
				if (!$this->object->getFieldDeclaration($field)->get('many_to_many')) continue;
				
				if (isset($params[$field])) {
					$fieldset = $field.'__set';
					$this->object->$fieldset->bulkAdd($params[$field]);
				}
				
			}
			
		}
		
		
		if (!$bok) {
			VMessages::_('Error', 'Speichern fehlgeschlagen', 'error');
		} else {
			VMessages::_('Ok', 'Speichern erfolgreich!', 'success');
		}
	}
}