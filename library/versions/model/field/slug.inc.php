<?php

class VModelFieldSlug extends VModelField {
	
	var $max_length = 50;
	
	var $reference = null;
	
	var $editable = false;
	
	public function __construct($options=array()) {
		parent::__construct($options);
	}
	
	public function onCreate($value) {
		return $this->generateSlug();
	}
	
	public function onUpdate($value) {
		return $this->generateSlug();
	}
	
	private function generateSlug() {
		if (is_null($this->get('reference'))) {
			return '';
		}
		
		VLoader::import('versions.utilities.string');
		
		return VString::sanitize( $this->get('_model')->get( $this->get('reference') ) );
	}
	
}