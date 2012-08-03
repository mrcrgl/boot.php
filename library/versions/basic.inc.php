<?php
/*
 * @build   28.01.2008
 * @project DMTp
 * @package Basic
 * 
 * @author  Marc Riegel
 * @contact mr@riegel.it
 * 
 * --
 * 
 * --
 */
abstract class VBasic {
  
  /*
   * @desc Fehlermeldung
   * @value array
   */
  private $errorMsgs = array();
  
  /*
   * @desc Fehlerzaehler
   * @value integer
   */
  private $iErrCounter=0;
  
  /*
   * config ini zeuch
   */
  private $conf = null;
  /*
   * @desc Gibt die Anzahl der Fehler zurueck
   */
  public final function getNumErrors() {
    return $this->iErrCounter;
  }
  
  /*
   * @desc Setzt die Fehler zurueck
   */
  public function resetErrors() {
    $this->errorMsgs     = array();
    $this->iErrorCounter = 0;
    return true;;
  }
  
  /*
   * @desc Gibt ein Array der Fehlermeldungen zurueck
   */
  public final function getErrorMsgs() {
    return $this->errorMsgs;
  }
  
  /*
   * @desc Gibt den String des letzten Fehlers zurueck
   */
  public final function getLastErrorMsg() {
    return $this->errorMsgs[$this->iErrCounter];
  }
  
  /*
   * @desc Setzt eine neue Fehlermeldung
   */
  protected function setErrorMsg($msg) {
    $this->iErrCounter++;
    $this->errorMsgs[$this->iErrCounter] = $msg;
  }
  
  function initConfig($file) {
  	$cfile = ENV::$config['confDir'].$file;
  	if (!file_exists($cfile)) {
  		return false;
  	}
  	$this->conf = parse_ini_file($cfile, true);
  }
  
	function getConfigVar($key) {
		
		$group = 'default';
		
		if (strpos($key, '.') !== false) {
			list ($group, $key) = explode('.', $key);
		}
		
		if (!isset($this->conf[$group])) {
			return null;
		}
		
		if (!isset($this->conf[$group][$key])) {
			return null;
		}
		
		return $this->conf[$group][$key];
	}
}

?>
