<?
print __FILE__." is deprecated";
include_once ENV::$config['smartyPluginsFile'];

$dispatcherName =  ((!isset(ENV::$config['dispatcher'])) ? "RequestDispatcher" : ENV::$config['dispatcher']);

Instance::_new(new $dispatcherName());

Instance::loadPersistent();

if (!Instance::f('Login')) {
  $refLogin = new Login();
  Instance::_new($refLogin, 'Login', true);
}

/*
 * Loading the Database-Models
 */

$DBC = new DBConfig();

foreach ($DBC->getInstances() as $db) {
  Instance::_new(new DBTransaction($db), 'db_'.$db);
}


if (!Instance::f('Language')) {
	$LanguageManager = new LanguageManager();
	Instance::_new($LanguageManager->getUserLanguage(), 'Language', true);
}

/*
 * Loading Global Instances
 */
#if (isset(ENV::$config['enableLang']) && ENV::$config['enableLang'] == true) Instance::_new(new Language(1), 'lang', true);
if (isset(ENV::$config['enableSettings']) && ENV::$config['enableSettings'] == true) Instance::_new(new Settings(), 'settings', true);
if (isset(ENV::$config['enableLinkGenerator']) && ENV::$config['enableLinkGenerator'] == true) Instance::_new(new LinkGenerator(), 'LinkGenerator');

ENV::$parse['_user']		 	= Instance::f('Login')->obj;
ENV::$parse['_language'] 	= Instance::f('Language');
ENV::$parse['_langcc'] 		= Instance::f('Language')->country_code;

if (Instance::f('Hotel') && Instance::f('Hotel')->isValid())
	ENV::$parse['_hotel'] 		= Instance::f('Hotel');


if (!function_exists('exception_handler')) {
  function exception_handler($exception) {
    //return false;
    
  	$errorMsg = str_replace("\#", "\\n", $exception->getMessage());
    $errorNumber = uniqid();
    $errorFile   = date("c").'-'.$errorNumber;
    
    try {
      if (isset(ENV::$config['logDir'])) {
        $errorlog = fopen(ENV::$config['logDir'].$errorFile.'.log', 'w');
      } else {
        $errorlog = fopen('log/'.$errorFile.'.log', 'w');
      }
      fwrite($errorlog, 'Fehler: '.$errorMsg."\n");
      fwrite($errorlog, 'Datum: '.date("c")."\n\n");
      
      fwrite($errorlog, '$_SERVER: '.var_export($_SERVER, true)."\n\n");
      fwrite($errorlog, '$_SESSION: '.var_export($_SESSION, true)."\n\n");
      fwrite($errorlog, '$_POST: '.var_export($_POST, true)."\n\n");
      fwrite($errorlog, '$_GET: '.var_export($_GET, true)."\n\n");
      fwrite($errorlog, '$_FILES: '.var_export($_FILES, true)."\n\n");
      fwrite($errorlog, '$_COOKIE: '.var_export($_COOKIE, true)."\n\n");
      
      $backtrace = "";
      $arrTrace = debug_backtrace();
      foreach ($arrTrace as $id => $row) {
      	$backtrace .= '#' . $id . '' . $row['file'] . '(' . $row['line'] . '): ' . $row['function'] . '(' . join(', ', $row['args']) . ')'."\n";
      }
      
      fwrite($errorlog, 'Backtrace:'."\n".$backtrace."\n\n");
      
      
      fclose($errorlog);
      
      #mail("exception@it-t.de", "[ERR] Unifuchs: $errorMsg", file_get_contents(ENV::$config['logDir'].$errorFile.'.log'),"from:unifuchs@dev3.de");
    } catch (Exception $e) {
      // Fehler beim schreiben der Log
    }
    #print $errorMsg;
    Instance::f('smarty')->assign('system_error', file_get_contents(ENV::$config['logDir'].$errorFile.'.log'));
    Instance::f('smarty')->assign('system_error_number', $errorNumber);
    
    Instance::f('smarty')->display('Fehler/fehler.htpl');
    
  }
}

set_exception_handler('exception_handler');

Instance::f($dispatcherName)->processRequest();

Instance::_unsetNonPersistent();


?>