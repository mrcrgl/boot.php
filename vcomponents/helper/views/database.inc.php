<?php

class ComponentHelperViewDatabase extends VApplicationView {
	
	public function show() {
		$document =& VFactory::getDocument();
		$document->setTemplate('database/index.htpl');
		
		try {
			$dbo =& VFactory::getDatabase();
			#$dbo->connect();
			
		} catch(Exception $e) {
			$document->assign('do_database_setup', true);
		}
		
	}
	
	public function show() {
		$document =& VFactory::getDocument();
		$document->setTemplate('database/index.htpl');
		
		/*
		try {
			$dbo =& VFactory::getDatabase();
			#$dbo->connect();
			
		} catch(Exception $e) {
			$document->assign('do_database_setup', true);
		}*/
		
	}
	
}