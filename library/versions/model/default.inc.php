<?php
/*
 * @build   28.01.2008
 * @project DMTp
 * @package DefaultObject
 *
 * @author  Marc Riegel
 * @contact mr@riegel.it
 *
 * --
 *
 * --
 */
abstract class VModelDefault extends VBasic {
  
  /*
   * @desc unique ID des Objektes,
   *       false wenn das objekt nicht valid ist.
   * @value integer
   */
  private $uid = false;
  
  
  /*
   * @desc Array mit Attributen des Objektes
   * @value array
   */
  private $attributes = array();
  
  
  /*
   * @desc Constructor stellt die m�glichkeit bereit
   *       Attributen direkt zu �bergeben oder eine UID zu definieren
   * @return bool
   */
  function __construct($attributes=false) {
    
    if (is_array($attributes)) {
      $this->importAttributes($attributes);
      return true;
    }
    
    if (Validator::is($attributes, 'uid') || Validator::is($attributes, 'hexuid')) {
      $this->setUID($attributes);
    }
    
  }
  
  
  /*
   * @desc Magic function __get fuer den Zugriff auf Attributen
   *       via $ref->varname
   * @return bool
   */
  function __get($memberName) {
    
  	$array = explode('_', $memberName);
  	if (array_pop($array) != 'uid') {
	  	$attr = $memberName.'_uid';
	  	if (Validator::is($this->$attr, 'hexuid')) {
	  		$parts = explode('_', $memberName);
	  		foreach ($parts as $k => $v) {
	  			$parts[$k] = ucfirst($v);
	  		}
	  		$classname = implode('', $parts);
	  		if (class_exists($classname, true)) {
	  			return new $classname($this->$attr);
	  		}
	  		
	  	} 
  	}
  	
  	if (isset($this->status) && $this->status == -9) {
      return "<a style=\"color: grey;\">(Daten gel&ouml;scht)</a>";
    }
    
    if ($memberName == 'uid') {
      return $this->uid;
    }
  	foreach ($this->attributes as $table => $bundle) {
      if ($this->getAttribute($memberName, $table) !== false) {
        return $this->getAttribute($memberName, $table);
      }
    }
    return null;
  }
  
  public function getType() {
    return get_class($this);
  }
  
  /*
   * @desc gibt die Unique ID
   * @return integer
   */
  protected function setUID($uid) {
    if (!Validator::is($uid, 'uid')) {
      return false;
    }
    
    $this->uid = $uid;
    $this->loadAttributesByUID($this->uid);
    
    return true;
  }
  
  /*
   * @desc Setzt die Unique ID und holt die Daten
   * @return bool
   */
  public function getUID() {
    return $this->uid;
  }
  
  /*
   * @desc prueft auf validitaet anhand der UID
   * @return bool
   */
  public function isValid() {
    if ($this->status == -9) {
      return false;
    }
    return (bool)$this->uid;
  }
  
  /*
   * @desc Importiert eine einzelne Attribute
   * @return bool
   */
  public function setAttribute($key, $value, $table='default') {
    if (!Validator::is($key, 'filled')) {
      return false;
    }
    
    if (!Validator::is($table, 'filled')) {
      return false;
    }
    
    if (!isset($this->attributes[$table]) || !is_array($this->attributes[$table])) {
      $this->attributes[$table] = array();
    }
    
    $this->attributes[$table][$key] = $value;
    
    return true;
  }
  
  /*
   * @desc gibt eine einzelne Attribute zurueck
   * @return mixed
   */
  public function getAttribute($key, $table='default') {
    if (!Validator::is($table, 'filled')) {
      return false;
    }
    
    if (!is_array($this->attributes[$table])) {
      return false;
    }
    
    if (!isset($this->attributes[$table][$key])) {
      return false;
    }
    
    return $this->attributes[$table][$key];
  }
  
  public function getAllAttributes() {
    $array = array();
    foreach ($this->attributes as $type => $tmp_array) {
      foreach ($tmp_array as $key => $value) {
        $array[$key] = $value;
      }
    }
    
    return $array;
  }
  
  /*
   * @desc Importiert die Attributen anhand eines Array's
   *       Standart: Eindimensionales Array mit Key und Value
   *       Extendet: Zweidimensionales Array, erste Stufe sind die Tables
   * @return bool
   */
  protected function importAttributes($arrAttributes, $table='default') {
    if (!is_array($arrAttributes)) {
      return false;
    }
    
    foreach ($arrAttributes as $key => $value) {
      if (is_array($value)) {
        $this->importAttributes($value, $key);
      } elseif ($key == 'uid') {
        $this->uid = $value;
      } else {
        $this->setAttribute($key, $value, $table);
      }
    }
    
    return true;
  }
  
  final function exportAttributes($table='default') {
    return $this->attributes[$table];
  }
  
  final function clearAttributes() {
    unset($this->attributes);
    $this->attributes = array();
    return true;
  }
  
  /*
   * @desc abstract zum laden der attributen anhand der UID
   */
  abstract protected function loadAttributesByUID($uid);
  
  protected function checkForm ($param, $arrRules=false) {
    $arrRules = (($arrRules) ? $arrRules : $this->_DataRules);
    $model    = array();
    
    if (isset($param)) {
      $bFormOk = true;
      
      $document =& VFactory::getDocument();
      
      foreach ($param as $key => $value) {
        if ($document)
          $document->assign($key.'_var', $value);
        if (isset($arrRules[$key])) {
          $model[($arrRules[$key]['2'])][$key] = $value;
					//if (!ereg($arrRules[$key]['1'], $value)) {
					if (substr($arrRules[$key]['1'], 0, 1) == ':') {
						
						if (!Validator::is($value, substr($arrRules[$key]['1'], 1))) {
							if ($arrRules[$key]['0'] == true || ($arrRules[$key]['0'] == false && strlen($value) > 0)) {
	              $bFormOk = false;
	              $this->setErrorMsg($key.'_err');
	              if ($document)
	                $document->assign($key.'_err', '1');
	            } // [/if]
						}
						
					} else if (!preg_quote($arrRules[$key]['1'], $value)) {
            if ($arrRules[$key]['0'] == true || ($arrRules[$key]['0'] == false && strlen($value) > 0)) {
              $bFormOk = false;
              $this->setErrorMsg($key.'_err');
              if ($document)
                $document->assign($key.'_err', '1');
            } // [/if]
          } // [/if]
        } // [/if]
      } // [/foreach]
    } // [/if]
    
    if ($bFormOk == false) {
      if ($document)
        $document->assign('error', '1');
    }
    return ($bFormOk == false) ? false : $model;
  }
  
  public function dump($addPre=false) {
    if ($addPre) {
      print "<pre>";
      var_dump($this);
      print "</pre>";
    } else {
      var_dump($this);
    }
  }
}
?>