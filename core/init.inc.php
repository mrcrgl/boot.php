<?php

$files = scandir(dirname(__FILE__).DIRECTORY_SEPARATOR.'init');
foreach ($files as $file) {
  if (preg_match('/\.inc\.php$/', $file)) {
  	//try {
  		require dirname(__FILE__).DIRECTORY_SEPARATOR.'init'.DIRECTORY_SEPARATOR.$file;
  		
  		if (class_exists('VDebug')) {
  			VDebug::_( new VDebugMessage( sprintf("loaded file: %s%s", $file, NL), DEBUG_MESSSAGE ) );
  		}
  		
  	/*} catch (Exception $e) {
  		
  		var_dump(debug_backtrace());
  		die('init.inc.php: while processing '.$file.' something goes wrong here.'.$e->getMessage());
  		
  	}*/
	}
}



