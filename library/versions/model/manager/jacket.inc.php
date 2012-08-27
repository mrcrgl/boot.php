<?php

class VModelManagerJacket {
	
	var $model;
	
	public function __construct(&$model) {
		$this->model =& $model;
	}
	
	public function getAll() {
		
		return $this->model->objects->fetch();
	}
	
}