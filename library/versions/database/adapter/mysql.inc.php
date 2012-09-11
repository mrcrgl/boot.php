<?php

class VDatabaseMysql extends VDatabase {

	private $strHost;
  private $strUser;
  private $strPass;
  protected $strDb;
  public $displayOnly = false;
  public $ignoreDatabaseException = false;

  // save the ressource - mysql connection
  private $resConnectionID = 0;

  // save the mysql error number and the own error message
  private $intErrNumber    = 0;
  private $strError    = "";

  // reference to db result
  private $refResult = null;

  // array for storing record information
  protected $arrRecord = "";

  private $sTable;
	private $result;
	public $debug = 0;

  /**
   *
   * the php5 constructor - get all data connection data
   *
   */
  function __construct($host=null, $database=null, $user=null, $pass=null) {
    #parent::__construct();
    // put the data into the related class vars

  	if (is_null($host) || is_null($database)) {
  		$host			= VSettings::f('database.host');
  		$database = VSettings::f('database.database');
  		$user			= VSettings::f('database.user');
  		$pass			= VSettings::f('database.password');
  	}

    $this->strHost    = $host;
    $this->strUser    = $user;
    $this->strPass    = $pass;
    $this->strDb      = $database;
  }

	function setTable($__table, $setBacksticks=false) {
		$this->sTable = ($setBacksticks == true) ? "`".$__table."`" : $__table;
	}

	/**
   * Starts a transaction
   *
   */
  public function startTransaction() {
    if ($this->bTransactionInProgress) {
      return true;
    }
    if ( !$this->query('BEGIN') ) {
      return false;
    }
    $this->bTransactionInProgress = true;
    return true;
  }

  /**
   * Commits a transaction
   *
   */
  public function commitTransaction() {
    if ( !$this->bTransactionInProgress ) {
      #print "moop";
      return false;
    }
    if ( !$this->query('COMMIT') ) {
      print $this->strError;
      $this->query('ROLLBACK');
      return false;
    }
    $this->bTransactionInProgress = false;
    return true;
  }

  public function userQuery($query) {
    $this->query($query);
    return true;
  }

	public function prepareQuery($__what = "*", $__where = "none", $__order = "none", $__limit = "none") {
    $this->selectRows($__what, $__where, $__order, $__limit);
    $this->bQueryPrepared = true;
  }

	/**
   * @param $sWhat = "*", $sWhere = "none", $sOrder = "none", $sLimit = "none"
   */
	function selectRows($sWhat = "*", $sWhere = "none", $sOrder = "none", $sLimit = "none") {
		$sQueryString = "SELECT $sWhat FROM {$this->sTable}";
		$sQueryString.= ($sWhere!="none") ? (" WHERE $sWhere ") : ("");
		$sQueryString.= ($sOrder!="none") ? (" ORDER BY $sOrder ") : ("");
		$sQueryString.= ($sLimit!="none") ? (" LIMIT $sLimit ") : ("");
		if ($this->debug == 1) {
			echo 'DB: '.$this->strDb.' - Query: '.$sQueryString;
		}
		try {
		  $this->query($sQueryString);
		} catch (Exception $e) {
		  throw new Exception($e);
		}
	}

  /**
   * $param string $__fields Fields to insert in
   * $param string $__values Values to insert in
   * $param bool   $__ignoreException Not in Use
   */
	function insertRow ($__fields,$__values, $__ignoreException=false) {
		$queryString = "INSERT INTO {$this->sTable} ($__fields) VALUES ($__values)";
		try {
		  $this->query($queryString);
		} catch (Exception $e) {
		  throw new Exception('DBLayer could not insert row: '.$e);
		}
	}

	function updateRow ($__what,$__where, $__ignoreException=false) {
		$queryString = "UPDATE {$this->sTable} SET $__what WHERE $__where";
	  try {
		  $this->query($queryString);
		} catch (Exception $e) {
		  throw new Exception('DBLayer could not update row: '.$e);
		}
	}

	function deleteRow ($__where) {
		$queryString = "DELETE FROM {$this->sTable} WHERE $__where";
	  try {
		  $this->query($queryString);
		} catch (Exception $e) {
		  throw new Exception('DBLayer could not delete row: '.$e);
		}
	}

	function getLastInsertID() {
		return $this->getLastID();
	}

	function getType() {
	  return mysqli_field_type($this->refResult);
	}

  /**
   *
   * open a connection to the database
   *
   */
  function connect() {

    // check if there is already an connection
    if($this->resConnectionID == 0) {

      // connect to database and save the connection_ressource_id
      $this->resConnectionID = mysqli_connect($this->strHost, $this->strUser, $this->strPass, $this->strDb);

      // if there is no connection - create error
      if(!$this->resConnectionID) {
        //$this->sql_abort("No Connection-Id - connection failed");
        throw new Exception("No Connection-Id - connection failed");
      }

      // select the wanted database
      if (!mysqli_select_db($this->resConnectionID, $this->strDb)) {
        //$this->sql_abort("Could not select ". $this->strDb, $this->resConnectionID);
        if ($this->ignoreDatabaseException == false)
        	throw new Exception("Could not select ". $this->strDb.' '.$this->resConnectionID);
      }

      // set connection collation
      if ( isset($this->queryForceUnicode) && $this->queryForceUnicode == true ) {
        $this->setConnectionCollationUnicode();
      }
    }

  }

  /**
   *
   * close the connection to the database
   *
   */
  function close() {

    // check if there is an res - if using php5 this will often be called twice 1. close 2. __destruct
    if(!is_null($this->resConnectionID)) {

      // close the connection to database
      $boolClose = mysqli_close($this->resConnectionID);

      // if close fails - print error message and abort
      if(!$boolClose) {
        $this->sql_abort("Cannot close the connection to ".$this->db);
      }

      $this->resConnectionID = null;

    }
  }

  /**
   *
   * abort script and print error message
   *
   */
  function sql_abort($strMessage) {
  	/**Tracker::error("Database Error:".$strMessage);
  	Tracker::error("MySQL Error:".$this->intErrNumber."(".$this->strError.")");
    Tracker::error("Host: ".$this->strHost." Database: ".$this->strDb." User: ".$this->strUser." PW: ***");*/
    $message =  sprintf("</td></tr></table>");
    $message .= sprintf("<b>Database error:</b> %s<br>\n", $strMessage);
    $message .= sprintf("<b>MySQL Error</b>: %s (%s)<br>\n",$this->intErrNumber,$this->strError);
    $message .= sprintf("<br>Host: {$this->strHost}<br>Database: {$this->strDb}<br>User: {$this->strUser}<br>PW: **** <br>");
    throw new Exception($message);

  }

  /**
   *
   * send a query and return a result (if there is one)
   *
   */
  protected function query($strQueryString) {
    if (!$this->displayOnly) {
      // send the query to database and save the ressource



      if (VSettings::f('default.debug')) {
        $profiler = VProfiler::getInstance('db');
      }

    	if (!$this->resConnectionID) {
    		$this->connect();
    	}

      /*if ($this->logCounter) {
        ENV::$countSQLQuerys[(preg_match('/^(SET|SELECT)/', $strQueryString)) ? "r" : "w"]++;
      }*/

      /*if ($this->logQuerys) {
        $mtime = microtime();
      }*/

      $refResult = mysqli_query($this->resConnectionID, $strQueryString);

      if (VSettings::f('default.debug')) {
        $profiler->mark($strQueryString);
      }

      /*if ($this->logQuerys) {
        ENV::$SQLQueryLog[] = array(
          'exec_time' => (microtime() - $mtime),
          'query'     => $strQueryString
        );
      }*/

    } else {
      $refResult = '-';
      echo $strQueryString.' - '.$this->resConnectionID.'<br />';
    }
    // set the error number and message of mysql
    $this->intErrNumber = mysqli_errno($this->resConnectionID);
    $this->strError = mysqli_error($this->resConnectionID);

    // if there is no ressource - die with error message
    if(!$refResult){

      //$this->sql_abort("DB: ".$this->strDb." - Query: ".$strQueryString);
      throw new Exception("Failure with query: <br /><br />".$this->sql_abort($strQueryString));

    }

    // return the ressource
    $this->refResult =  $refResult;
    return $this->refResult;
  }

	function getErrorNumber() {
		$this->intErrNumber;
	}

	function getErrorMsg() {
		$this->strError;
	}

	function freeResult() {
	  mysqli_free_result($this->refResult);
	}
	/*function userQuery($strQueryString) {
    return $this->query($strQueryString);
  }*/

	function f($sName) {
		//return utf8_decode($this->arrRecord[$sName]);
    return $this->arrRecord[$sName];
	}

  function nextRecord() {
		if (!$this->refResult) {
		  return false;
		}
    $this->arrRecord = mysqli_fetch_assoc($this->refResult);
		if (!is_array($this->arrRecord)) {
			mysqli_free_result($this->refResult);
			$this->refResult = 0;
			return false;
		} else {
			return true;
		}
	}

	function escape($value) {
		if (!$this->resConnectionID) {
    	$this->connect();
    }
		return mysqli_real_escape_string($this->resConnectionID, $value);
	}

  /**
   *
   * return the id of the last inserted row
   *
   */
  function getLastID(){

    // return the id of the last inserted row
    return intval(mysqli_insert_id($this->resConnectionID));

  }

  /**
   *
   * count the number of affected rows
   *
   */
  function getNumRows() {

    // return the number of affected rows
    return intval(mysqli_affected_rows($this->resConnectionID));

  }

  function createDatabaseIfNotExists() {
  	$this->userQuery("CREATE DATABASE IF NOT EXISTS ".$this->strDb." DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;");
  }

  function dropDatabase(){
  	$this->userQuery("DROP DATABASE ".$this->strDb);
  }

  /**
   *
   * function to get an array with all table, the db contains
   *
   */
  function getListOfTables() {

    // the sql-query for getting all tables out of this db
    $strSql = "SHOW TABLES FROM $this->strDb";

    // send the query to database and fetch the ressource
    $resSqlResult = $this->query($strSql);

    // create an empty array
    $arrTableList = array();

    // loop - "make" out of each line of the ressource an array
    while($arrTable = mysqli_fetch_array($resSqlResult)) {

      // push the data into the empty array
      array_push($arrTableList, $arrTable[0]);

    }

    // return the array, which contains now all data
    return $arrTableList;

  }

  /**
   *
   * function to get an array with all columns of givin table
   *
   */
  function getListOfColumns($table) {

    // the sql-query for getting all tables out of this db
    $strSql = sprintf("SHOW COLUMNS FROM `%s`", $table);

    // send the query to database and fetch the ressource
    $resSqlResult = $this->query($strSql);

    // create an empty array
    $arrTableList = array();

    // loop - "make" out of each line of the ressource an array
    while($arrTable = mysqli_fetch_array($resSqlResult)) {

      // push the data into the empty array
      array_push($arrTableList, $arrTable[0]);

    }

    // return the array, which contains now all data
    return $arrTableList;

  }

  /**
   * Returns the version of the MySQL db used
   *
   * @return string
   */
  public function getVersion() {
    $this->query("SELECT VERSION()");
    $this->nextRecord();
    $array = $this->getRecord();
    return array_pop($array);
  }

	function getRecord() {
		return $this->arrRecord;
	}

  public function setAutoCommit($b=false) {
    $choose = ($b === false) ? "0" : "1";
    $this->query("SET autocommit=$choose");
  }

  public function doCommit() {
    $this->query("COMMIT");
  }

  public function setConnectionCollationUnicode() {
    // Check if the used MySQL version supports charsets
    list($v_upper, $v_major, $v_minor) = explode('.', $this->getVersion());
    if ( ($v_upper >= 5) || ($v_upper >= 4 && $v_major >= 1) ) {
      $this->query("SET character_set_client = utf8");
      $this->query("SET character_set_results = utf8");
      $this->query("SET character_set_connection = utf8");
    }
    return true;
  }

  /**
   *
   * destructor for php5
   *
   */
  function __destruct() {

    //$this->close();

  }


}