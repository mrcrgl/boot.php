<?php 

class VModelStructure extends VObject {
	
	#var $_field_references = array();
	
	public function __construct($__uid) {
		
		$this->initialize();
		
	}
	
	private function initialize() {
		VModelField::prepareModel(get_class($this));
		print "<pre>";
    var_dump(VModelField::$_instances);
    print "</pre>";
	}
	
	public function bulkUpdate($params) {
		
	}
	
	public function save() {
		
	}
	
	public function set() {
		
	}
	
	public function get() {
		
	}
}
