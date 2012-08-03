<?php
/*
 * @build   13.03.2008
 * @project DMTp
 * @package package_name
 *
 * @author  Marc Riegel
 * @contact mr@riegel.it
 *
 * --
 * Validates a different Type of Values
 * --
 */
class Validator {
  
  public static $error;
  public static $errors;
  
  public static function setError($error) {
    Validator::$error    = $error;
    Validator::$errors[] = $error;
  }
  
  public static function resetErrors() {
    Validator::$error    = '';
    Validator::$errors[] = array();
  }
  
  public static function is($value, $processor, $minlen=false, $maxlen=false) {
    if ($minlen && strlen($value) < $minlen) {
      Validator::setError("MinLengthExceeded");
      return false;
    }
    
    if ($maxlen && strlen($value) > $maxlen) {
      Validator::setError("MaxLengthExceeded");
      return false;
    }
    
    $processor = 'is_'.$processor;
    if (!Validator::$processor($value)) {
      return false;
    }
    
    return true;
  }
  
  public static function regexp($value, $regexp, $isTrueIfEmpty=false, $isCaseSensitive=false) {
    if (!Validator::is($value, 'filled') && $isTrueIfEmpty) {
      true;
    }
    
    if ($isCaseSensitive) {
      if (!ereg($regexp, $value)) {
        Validator::setError("ExpressionNotMatched");
        return false;
      }
    } else {
      if (!eregi($regexp, $value)) {
        Validator::setError("ExpressionNotMatched");
        return false;
      }
    }
    
    return false;
  }
  
  
  /***************************************************
   *
   * Validator Module
   *
   ***************************************************/
  
  public static function is_integer($value) {
    if (is_numeric($value)) {
      return true;
    }
    
    Validator::setError("IntegerExpected");
    return false;
  }
  
  public static function is_filled($value) {
    if (strlen($value) > 0) {
      return true;
    }
    
    Validator::setError("StringIsEmpty");
    return false;
  }
  
  public static function is_uid($value) {
    if (self::is_intuid($value)) {
      return true;
    }
    if (self::is_hexuid($value)) {
      return true;
    }
    
    Validator::setError("UIDExpected");
    return false;
  }
  
  public static function is_intuid($value) {
    if (is_numeric($value)) {
      return true;
    }
    
    Validator::setError("UIDExpected");
    return false;
  }
  
  public static function is_hexuid($value) {
    if (preg_match('/^([0-9a-z]{13})$/', $value)) {
      return true;
    }
    
    Validator::setError("HexUIDExpected");
    return false;
  }
  
  public static function is_email($value) {
    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
      return true;
    }
    
    Validator::setError("EMailExpected");
    return false;
  }
  
  public static function is_ip($value) {
    if (filter_var($value, FILTER_VALIDATE_IP)) {
      return true;
    }
    
    Validator::setError("IPExpected");
    return false;
  }
  
  public static function is_timestamp($value) {
    if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $value, $matches)) { 
      if (checkdate($matches[2], $matches[3], $matches[1])) { 
        return true; 
      } 
    } 
    
    Validator::setError("MYSQL_TIMESTAMP_EXPECTED");
    return false;
  }
  
  
}


?>
