<?php

class ComponentHelperViewDatabase extends VApplicationView {
	
	public function show() {
		$document =& VFactory::getDocument();
		$document->setTemplate('database/index.htpl');
		$document->assign('_current_step_tpl', 'database/step/check.htpl');
		
		try {
			$dbo =& VFactory::getDatabase();
			#$dbo->connect();
			
		} catch(Exception $e) {
			$document->assign('do_database_setup', true);
		}
		
		
	}
	
	public function configure() {
		$document =& VFactory::getDocument();
		$input    =& VFactory::getInput();
		
		$document->setTemplate('database/index.htpl');
		$document->assign('_current_step_tpl', 'database/step/configure.htpl');
		
		if (strtolower($input->getMethod()) == 'post') {
			
		}
		
		
	}
	
}