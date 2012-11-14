<?php

class ComponentHelperViewDatabase extends BApplicationView 
{
    
    public function show()
 {
        $oDocument =& BFactory::getDocument();
        $oDocument->setTemplate('database/index.htpl');
        $oDocument->assign('_current_step_tpl', 'database/step/check.htpl');
        
        try {
            $dbo =& BFactory::getDatabase();
            $dbo->connect();
            
        } catch (Exception $e) {
            $oDocument->assign('do_database_setup', true);
        }
        
        
    }
    
    public function configure()
        {
        $oDocument =& BFactory::getDocument();
        $input    =& BFactory::getInput();
        $session  =& BFactory::getSession();
        
        $oDocument->setTemplate('database/index.htpl');
        $oDocument->assign('_current_step_tpl', 'database/step/configure.htpl');
        
        $oDocument->assign('db_host', $oSession->get('db_host', '', 'formvalue'));
        $oDocument->assign('db_main_user', $oSession->get('db_main_user', '', 'formvalue'));
        $oDocument->assign('db_main_pass', $oSession->get('db_main_pass', '', 'formvalue'));
        
        if (strtolower($oInput->getMethod()) == 'post') {
            
            $db_host             = $oInput->get('db_host', '', 'post');
            #$db_database     = $oInput->get('db_database', '', 'post');
            $db_main_user = $oInput->get('db_main_user', '', 'post');
            $db_main_pass = $oInput->get('db_main_pass', '', 'post');
            
            $oSession->set('db_host', $db_host, 'formvalue');
            $oSession->set('db_main_user', $db_main_user, 'formvalue');
            $oSession->set('db_main_pass', $db_main_pass, 'formvalue');
            
            $oDocument->assign('db_host', $db_host);
            #$oDocument->assign('db_database', $db_database);
            $oDocument->assign('db_main_user', $db_main_user);
            $oDocument->assign('db_main_pass', $db_main_pass);
            
            #$dbo =& BDatabase::getInstance('mysql', $db_host, $db_database, $db_main_user, $db_main_pass);
            
            
            $dbo = mysql_connect($db_host, $db_main_user, $db_main_pass, true);
            if (!$dbo) {
                BMessages::_("Error", sprintf("Die angegebenen Daten sind nicht g&uuml;tig: %s", mysql_error()), 'error');
                mysql_close($dbo);
                return false;
            }
            
            if (!mysql_stat($dbo)) {
                BMessages::_("Error", sprintf("Die angegebenen Daten sind nicht g&uuml;tig: %s", mysql_error()), 'error');
                mysql_close($dbo);
                return false;
            }
            
            BMessages::_("Ok", sprintf("Verbindung konnte erfolgreich hergestellt werden."), 'success');
            
            header( sprintf("Location: /%sdatabase/create", $oDocument->getUrlPrefix()) );
            exit;
            
        }
        
    }
    
    public function create()
            {
        $oDocument =& BFactory::getDocument();
        $input    =& BFactory::getInput();
        $session  =& BFactory::getSession();
        
        $oDocument->setTemplate('database/index.htpl');
        $oDocument->assign('_current_step_tpl', 'database/step/create.htpl');
        
        $oDocument->assign('db_host', $oSession->get('db_host', '', 'formvalue'));
        $oDocument->assign('db_main_user', $oSession->get('db_main_user', '', 'formvalue'));
        $oDocument->assign('db_main_pass', $oSession->get('db_main_pass', '', 'formvalue'));
        
        $oDocument->assign('create_user', $oSession->get('create_user', '', 'formvalue'));
        $oDocument->assign('db_database', $oSession->get('db_database', '', 'formvalue'));
        $oDocument->assign('db_user', $oSession->get('db_user', '', 'formvalue'));
        $oDocument->assign('db_pass', $oSession->get('db_pass', '', 'formvalue'));
        
        
        if (strtolower($oInput->getMethod()) == 'post') {
            
            $db_host             = $oInput->get('db_host', '', 'post');
            $db_main_user = $oInput->get('db_main_user', '', 'post');
            $db_main_pass = $oInput->get('db_main_pass', '', 'post');
            
            $create_user         = $oInput->get('create_user', 0, 'post');
            $db_database         = $oInput->get('db_database', null, 'post');
            $db_user                 = $oInput->get('db_user', null, 'post');
            $db_pass                 = $oInput->get('db_pass', null, 'post');
            
            
            $oSession->set('db_host', $db_host, 'formvalue');
            $oSession->set('db_main_user', $db_main_user, 'formvalue');
            $oSession->set('db_main_pass', $db_main_pass, 'formvalue');
            
            $oSession->set('create_user', $create_user, 'formvalue');
            $oSession->set('db_database', $db_database, 'formvalue');
            $oSession->set('db_user', $db_user, 'formvalue');
            $oSession->set('db_pass', $db_pass, 'formvalue');
            
            $oDocument->assign('db_host', $db_host);
            $oDocument->assign('db_main_user', $db_main_user);
            $oDocument->assign('db_main_pass', $db_main_pass);
            
            $oDocument->assign('create_user', $create_user);
            $oDocument->assign('db_database', $db_database);
            $oDocument->assign('db_user', $db_user);
            $oDocument->assign('db_pass', $db_pass);
            
            $dbo = mysql_connect($db_host, $db_main_user, $db_main_pass, true);
            if (mysql_select_db($db_database, $dbo)) {
                BMessages::_("Error", sprintf("Die angegebenen Datenbank existiert bereits: %s", $db_database), 'error');
                mysql_close($dbo);
                return false;
            }
            
            if ($create_user && !$db_user) {
                BMessages::_("Error", sprintf("Bitte geben Sie einen Benutzer ein f&uuml;r die Datenbank."), 'error');
                return false;
            }
            
            if ($create_user && !$db_pass) {
                BLoader::import('versions.utilities.password');
                $db_pass = BPassword::create(12);
                BMessages::_("Notice", sprintf("Generiertes Passwort: %s", $db_pass));
                $oSession->set('db_pass', $db_pass, 'formvalue');
                $oDocument->assign('db_pass', $db_pass);
            }
            
            if (mysql_query(sprintf("CREATE DATABASE `%s` DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci", $db_database), $dbo)) {
                BMessages::_("OK", sprintf("Datenbank %s wurde erfolgreich erstellt.", $db_database), 'success');
            } else {
                BMessages::_("Error", sprintf("Datenbank %s wurde nicht erstellt: %s", $db_database, mysql_error($dbo)), 'error');
                mysql_close($dbo);
                return false;
            }
            
            if ($create_user) {
                if (mysql_query(sprintf("CREATE USER '%s'@'%%' IDENTIFIED BY '%s'", $db_user, $db_pass), $dbo)) {
                    BMessages::_("OK", sprintf("Benutzer %s wurde erfolgreich erstellt.", $db_user), 'success');
                } else {
                    BMessages::_("Error", sprintf("Benutzer %s wurde nicht erstellt: %s", $db_user, mysql_error($dbo)), 'error');
                    mysql_close($dbo);
                    return false;
                }
                if (mysql_query(sprintf("GRANT ALL PRIVILEGES ON `%s`.* TO '%s'@'%%' WITH GRANT OPTION", $db_database, $db_user), $dbo)) {
                    BMessages::_("OK", sprintf("Zugriffsrechte f&uuml;r %s@%% wurde erfolgreich erteilt.", $db_user), 'success');
                } else {
                    BMessages::_("Error", sprintf("Zugriffsrechte f�r %s@%% wurden nicht erteilt: %s", $db_user, mysql_error($dbo)), 'error');
                    mysql_close($dbo);
                    return false;
                }
            }
            
            
            mysql_close($dbo);
            
            header( sprintf("Location: /%sdatabase/showconfig", $oDocument->getUrlPrefix()) );
            exit;
            
        }
    }
    
    public function showconfig()
            {
        $oDocument =& BFactory::getDocument();
        $input    =& BFactory::getInput();
        $session  =& BFactory::getSession();
        
        $oDocument->setTemplate('database/index.htpl');
        $oDocument->assign('_current_step_tpl', 'database/step/showconfig.htpl');
        
        $oDocument->assign('db_host', $oSession->get('db_host', '', 'formvalue'));
        $oDocument->assign('db_main_user', $oSession->get('db_main_user', '', 'formvalue'));
        $oDocument->assign('db_main_pass', $oSession->get('db_main_pass', '', 'formvalue'));
        
        $oDocument->assign('create_user', $oSession->get('create_user', '', 'formvalue'));
        $oDocument->assign('db_database', $oSession->get('db_database', '', 'formvalue'));
        $oDocument->assign('db_user', $oSession->get('db_user', '', 'formvalue'));
        $oDocument->assign('db_pass', $oSession->get('db_pass', '', 'formvalue'));
        
    }
    
}