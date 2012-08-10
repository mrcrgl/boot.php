<?php

VLoader::import('versions.utilities.string');

class ComponentCrudViewModel extends VApplicationView {
	
	var $object_name = null;
	
	var $object_uid = null;
	
	var $object_manager_name = null;
	
	var $object = null;
	
	var $object_manager = null;
	
	public function create() {
		$this->fetchObject();
		
		$document =& VFactory::getDocument();
		$renderer =& $document->getRenderer();
		$renderer->appendTemplateDirPart( $this->getAlternateTemplatePath() );
		
		$document->setTemplate('crud/create.htpl');
  	$document->assign('user_defined_template', $this->getAlternateTemplate('create.htpl'));
  	
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
  	$document->assign('user_defined_template', $this->getAlternateTemplate('read.htpl'));
  	
  	
	}
	
	public function update() {
		$this->fetchObject();
		
		$document =& VFactory::getDocument();
  	$renderer =& $document->getRenderer();
		$renderer->appendTemplateDirPart( $this->getAlternateTemplatePath() );
		
		$document->setTemplate('crud/create.htpl');
  	$document->assign('user_defined_template', $this->getAlternateTemplate('create.htpl'));
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
  	$this->object_manager_name = $this->object_name.'Manager';
  	
  	if (!$this->object_name) {
  		throw new Exception( "Input object_name must be set!" );
  	}
  	
  	$this->object = new $this->object_name($this->object_uid);
  	$this->object_manager = new $this->object_manager_name();
  	
  	$this->object_manager->set('ignore_object_state', true);
  	
  	$document =& VFactory::getDocument();
  	
  	if ($parent) {
  		$this->object_manager->filter($parent, $input->get($parent, null, 'get'));
  		$document->assign($parent, $input->get($parent, null, 'get'));
  	}
  	
  	
  	$document->assign('object', &$this->object);
  	$document->assign('manager', &$this->object_manager);
  	
	}
	
	private function getAlternateTemplate($__method) {
		$path = strtolower(implode(DS, VString::splitCamelCase($this->object_name)));
  	
		
  	$newpath = 'crud'.DS.substr($path, strpos($path, '/model/')+strlen('/model/')).DS.$__method;
  	return $newpath;
	}
	
	private function getAlternateTemplatePath() {
		$path = strtolower(implode(DS, VString::splitCamelCase($this->object_name)));
  	
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
		$bok = $this->object->update($params);
		
		if (!$bok) {
			VMessages::_('Error', 'Speichern fehlgeschlagen', 'error');
		} else {
			VMessages::_('Ok', 'Speichern erfolgreich!', 'success');
		}
	}
}