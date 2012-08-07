<?php

class ComponentHelperViewDatabase extends VApplicationView {
	
	public function show() {
		$document =& VFactory::getDocument();
		$document->setTemplate('database/index.htpl');
		$document->assign('_current_step_tpl', 'database/step/check.htpl');
		
		try {
			$dbo =& VFactory::getDatabase();
			$dbo->connect();
			
		} catch(Exception $e) {
			$document->assign('do_database_setup', true);
		}
		
		
	}
	
	public function configure() {
		$document =& VFactory::getDocument();
		$input    =& VFactory::getInput();
		$session  =& VFactory::getSession();
		
		$document->setTemplate('database/index.htpl');
		$document->assign('_current_step_tpl', 'database/step/configure.htpl');
		
		$document->assign('db_host', $session->get('db_host', '', 'formvalue'));
		$document->assign('db_main_user', $session->get('db_main_user', '', 'formvalue'));
		$document->assign('db_main_pass', $session->get('db_main_pass', '', 'formvalue'));
		
		if (strtolower($input->getMethod()) == 'post') {
			
			$db_host 			= $input->get('db_host', '', 'post');
			#$db_database 	= $input->get('db_database', '', 'post');
			$db_main_user = $input->get('db_main_user', '', 'post');
			$db_main_pass = $input->get('db_main_pass', '', 'post');
			
			$session->set('db_host', $db_host, 'formvalue');
			$session->set('db_main_user', $db_main_user, 'formvalue');
			$session->set('db_main_pass', $db_main_pass, 'formvalue');
			
			$document->assign('db_host', $db_host);
			#$document->assign('db_database', $db_database);
			$document->assign('db_main_user', $db_main_user);
			$document->assign('db_main_pass', $db_main_pass);
			
			#$dbo =& VDatabase::getInstance('mysql', $db_host, $db_database, $db_main_user, $db_main_pass);
			
			
			$dbo = mysql_connect($db_host, $db_main_user, $db_main_pass, true);
			if (!$dbo) {
				VMessages::_("Error", sprintf("Die angegebenen Daten sind nicht g&uuml;tig: %s", mysql_error()), 'error');
				mysql_close($dbo);
				return false;
			}
			
			if (!mysql_stat($dbo)) {
				VMessages::_("Error", sprintf("Die angegebenen Daten sind nicht g&uuml;tig: %s", mysql_error()), 'error');
				mysql_close($dbo);
				return false;
			}
			
			VMessages::_("Ok", sprintf("Verbindung konnte erfolgreich hergestellt werden."), 'success');
			
			header( sprintf("Location: /%sdatabase/create", $document->getUrlPrefix()) );
			exit;
			
		}
		
	}
	
	public function create() {
		$document =& VFactory::getDocument();
		$input    =& VFactory::getInput();
		$session  =& VFactory::getSession();
		
		$document->setTemplate('database/index.htpl');
		$document->assign('_current_step_tpl', 'database/step/create.htpl');
		
		$document->assign('db_host', $session->get('db_host', '', 'formvalue'));
		$document->assign('db_main_user', $session->get('db_main_user', '', 'formvalue'));
		$document->assign('db_main_pass', $session->get('db_main_pass', '', 'formvalue'));
		
		$document->assign('create_user', $session->get('create_user', '', 'formvalue'));
		$document->assign('db_database', $session->get('db_database', '', 'formvalue'));
		$document->assign('db_user', $session->get('db_user', '', 'formvalue'));
		$document->assign('db_pass', $session->get('db_pass', '', 'formvalue'));
		
		
		if (strtolower($input->getMethod()) == 'post') {
			
			$db_host 			= $input->get('db_host', '', 'post');
			$db_main_user = $input->get('db_main_user', '', 'post');
			$db_main_pass = $input->get('db_main_pass', '', 'post');
			
			$create_user 		= $input->get('create_user', 0, 'post');
			$db_database 		= $input->get('db_database', null, 'post');
			$db_user 				= $input->get('db_user', null, 'post');
			$db_pass 				= $input->get('db_pass', null, 'post');
			
			
			$session->set('db_host', $db_host, 'formvalue');
			$session->set('db_main_user', $db_main_user, 'formvalue');
			$session->set('db_main_pass', $db_main_pass, 'formvalue');
			
			$session->set('create_user', $create_user, 'formvalue');
			$session->set('db_database', $db_database, 'formvalue');
			$session->set('db_user', $db_user, 'formvalue');
			$session->set('db_pass', $db_pass, 'formvalue');
			
			$document->assign('db_host', $db_host);
			$document->assign('db_main_user', $db_main_user);
			$document->assign('db_main_pass', $db_main_pass);
			
			$document->assign('create_user', $create_user);
			$document->assign('db_database', $db_database);
			$document->assign('db_user', $db_user);
			$document->assign('db_pass', $db_pass);
			
			$dbo = mysql_connect($db_host, $db_main_user, $db_main_pass, true);
			if (mysql_select_db($db_database, $dbo)) {
				VMessages::_("Error", sprintf("Die angegebenen Datenbank existiert bereits: %s", $db_database), 'error');
				mysql_close($dbo);
				return false;
			}
			
			if ($create_user && !$db_user) {
				VMessages::_("Error", sprintf("Bitte geben Sie einen Benutzer ein f&uuml;r die Datenbank."), 'error');
				return false;
			}
			
			if ($create_user && !$db_pass) {
				VLoader::import('versions.utilities.password');
				$db_pass = VPassword::create(12);
				VMessages::_("Notice", sprintf("Generiertes Passwort: %s", $db_pass));
				$session->set('db_pass', $db_pass, 'formvalue');
				$document->assign('db_pass', $db_pass);
			}
			
			if (mysql_query(sprintf("CREATE DATABASE `%s` DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci", $db_database), $dbo)) {
				VMessages::_("OK", sprintf("Datenbank %s wurde erfolgreich erstellt.", $db_database), 'success');
			} else {
				VMessages::_("Error", sprintf("Datenbank %s wurde nicht erstellt: %s", $db_database, mysql_error($dbo)), 'error');
				mysql_close($dbo);
				return false;
			}
			
			if ($create_user) {
				if (mysql_query(sprintf("CREATE USER '%s'@'%%' IDENTIFIED BY '%s'", $db_user, $db_pass), $dbo)) {
					VMessages::_("OK", sprintf("Benutzer %s wurde erfolgreich erstellt.", $db_user), 'success');
				} else {
					VMessages::_("Error", sprintf("Benutzer %s wurde nicht erstellt: %s", $db_user, mysql_error($dbo)), 'error');
					mysql_close($dbo);
					return false;
				}
				if (mysql_query(sprintf("GRANT ALL PRIVILEGES ON `%s`.* TO '%s'@'%%' WITH GRANT OPTION", $db_database, $db_user), $dbo)) {
					VMessages::_("OK", sprintf("Zugriffsrechte f&uuml;r %s@%% wurde erfolgreich erteilt.", $db_user), 'success');
				} else {
					VMessages::_("Error", sprintf("Zugriffsrechte fŸr %s@%% wurden nicht erteilt: %s", $db_user, mysql_error($dbo)), 'error');
					mysql_close($dbo);
					return false;
				}
			}
			
			
			mysql_close($dbo);
			
			header( sprintf("Location: /%sdatabase/showconfig", $document->getUrlPrefix()) );
			exit;
			
		}
	}
	
	public function showconfig() {
		$document =& VFactory::getDocument();
		$input    =& VFactory::getInput();
		$session  =& VFactory::getSession();
		
		$document->setTemplate('database/index.htpl');
		$document->assign('_current_step_tpl', 'database/step/showconfig.htpl');
		
		$document->assign('db_host', $session->get('db_host', '', 'formvalue'));
		$document->assign('db_main_user', $session->get('db_main_user', '', 'formvalue'));
		$document->assign('db_main_pass', $session->get('db_main_pass', '', 'formvalue'));
		
		$document->assign('create_user', $session->get('create_user', '', 'formvalue'));
		$document->assign('db_database', $session->get('db_database', '', 'formvalue'));
		$document->assign('db_user', $session->get('db_user', '', 'formvalue'));
		$document->assign('db_pass', $session->get('db_pass', '', 'formvalue'));
		
	}
	
}