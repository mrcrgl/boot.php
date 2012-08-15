<?php

class VModelManagerJacket {
	
	var $model;
	
	public function __construct(&$model) {
		$this->model =& $model;
	}
	
	public function getAll() {
		$designer = VDatabaseDesigner::getInstance();
		return $designer->getModels(&$this->model);
	}
	
}