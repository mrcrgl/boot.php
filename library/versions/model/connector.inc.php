<?php
/*
 * @build   13.03.2008
 * @project DMTp
 * @package DefaultConnector
 *
 * @author  Marc Riegel
 * @contact mr@riegel.it
 *
 * --
 *
 * --
 */
abstract class VModelConnector extends VModelDefault 
{

  /*
   * The Database (DBTransaction) Object
   */
  private $objDB;
  private $usingHexUid = false;

  var $default_datatype = "varchar(255) NOT NULL";

  var $predefined_fields = array(
      'uid'                 => "varchar(13) NOT NULL",
      'puid'                 => "varchar(13) NOT NULL",
      'duid'                 => "varchar(13) NOT NULL",
      'ts_create'     => "TIMESTAMP DEFAULT 0",
      'ts_update'     => "TIMESTAMP DEFAULT 0",
      'status'             => "tinyint(1) NOT NULL DEFAULT '1'",
      'title'             => "varchar(128) NOT NULL",
      'description' => "text NOT NULL",
      'key'                 => "varchar(128) NOT NULL",
      'value'             => "varchar(255) NOT NULL"

  );

  var $predefined_datatypes = array(
      'timestamp'        => 'TIMESTAMP %s DEFAULT %s'
  );

  var $predefined_indexes = array(
      'uid' => array(
          '_type' => 'UNIQUE KEY',
          '_fields' => array('uid')
      ),
      'puid' => array(
          '_type' => 'KEY',
          '_fields' => array('puid')
      ),
      'duid' => array(
          '_type' => 'KEY',
          '_fields' => array('duid')
      ),
      'status' => array(
          '_type' => 'KEY',
          '_fields' => array('status')
      ),
      'key' => array(
          '_type' => 'KEY',
          '_fields' => array('key')
      ),
      'value' => array(
          '_type' => 'KEY',
          '_fields' => array('value')
      ),
      'title' => array(
          '_type' => 'KEY',
          '_fields' => array('title')
      ),
      'locale_uid' => array(
          '_type' => 'KEY',
          '_fields' => array('locale_uid')
      )
  );

  public function __construct($attributes=false)
      {
    // make ur checks
    if (!isset($this->_DataMap) || !Validator::is($this->_DataMap, 'array')) {
      throw new Exception("Var DataMap is Missing in Object '".get_class($this)."'.\n");
    }
    if (!isset($this->_DataRules) || !Validator::is($this->_DataRules, 'array')) {
      throw new Exception("Var DataRules is Missing in Object '".get_class($this)."'.\n");
    }
    if (isset($this->_DataMap['_usehexuid']) && $this->_DataMap['_usehexuid'] === true) {
      $this->usingHexUid = true;
    }

    parent::__construct($attributes);
  }

    public function getModelVersion()
  {
        return 1;
    }

  public function insert($param, $what='default')
    {
    $this->update($param, $what);
  }

  public function update($param, $dontCheckNeedles=false)
  {
    if ($dontCheckNeedles === true) {
      foreach ($this->_DataRules as $field => $rules) {
        if ($rules[0] === true && !isset($param[$field])) {
          $param[$field] = $this->$field;
        }
      }
    }

    /*
     *
     */
    foreach ($param as $key => $value) {
        if (Validator::is($value, 'array') && isset($this->_DataRules[($key.'_uid')])) {
            $attribute = $key.'_uid';
            $parts = explode('_', $key);
            $classname = "";
            foreach ($parts as $pkey => $pval) {
                $classname .= ucfirst($pval);
            }
            VLoader::autoload($classname);
            if (!class_exists($classname)) 
{
                array_shift($parts);
                $classname = "";
                foreach ($parts as $pkey => $pval) {
                    $classname .= ucfirst($pval);
                }

                VLoader::autoload($classname);
                if (!class_exists($classname)) 
{
                    continue;
                }
            }


            try {
                $obj = new $classname( (($this->isValid()) ? $this->$attribute : false) );
            } catch (Exception $e) {
                $obj = null;
                continue;
            }

            $obj->update($value);
            $param[$attribute] = $obj->uid;
        }
    }



    $model = $this->checkForm($param);
    if ($model == false || empty($model)) {
      return false;
    }

    /*
     * bOnCreate
     * used for specials 'oncreate'
     */
    $bOnCreate = false;

    if ( !$this->isValid() ) {
      $this->openDatabase();
      $this->createNewAtDatabase();
      $bOnCreate = true;
    }

    /*
     * Transaction begin
     */
    $this->openDatabase();
    if ( !$this->objDB->startTransaction() ) {
      die("startTransaction failed! (".$this->objDB->strError.")<br />");
      return false;
    }

    foreach ($model as $relationTable => $param) {
      $this->updateRelation($param, $relationTable, $bOnCreate);
    }

    /*
     * Transaction end
     */
    if ( !$this->objDB->commitTransaction() ) {
      die("commitTransaction failed! (".$this->objDB->intError." - ".$this->objDB->strError.")<br />");
      return false;
    }

    $this->closeDatabase();

    $this->reloadAttributes();

    /**
     * User Log
     */
    if (get_class($this) != 'UserLog' && is_object(VInstance::f('Login')) && is_object(Instance::f('Login')->obj)) 
{
      VInstance::f('Login')->obj->log($this, (($bOnCreate === true) ? "Object created." : "Object updated."));
    }

    return true;
  }

  public function isSqlInstalled()
  {
      $datamap = $this->_DataMap;
      $is_installed = true;

      $this->objDB =& VFactory::getDatabase();
      $tables_installed = $this->objDB->getListOfTables();

      foreach ($datamap as $key => $attributes) {
          if (!Validator::is($attributes, 'array') || preg_match('/^_/', $key)) continue;

          #printf("Required: %s".NL, $attributes['_table']);
          if (!in_array($attributes['_table'], $tables_installed)) {
              $is_installed = false;
          }
      }


      return $is_installed;
  }

    public function isSqlUpToDate()
  {
      $tablecolumns = $this->getTableColumns();
      $is_uptodate = true;

      $this->objDB =& VFactory::getDatabase();
      #$tables_installed = $this->objDB->getListOfTables();

      foreach ($tablecolumns as $table => $array_columns) {

          $columns = $this->objDB->getListOfColumns($table);

          if (count($columns) != count($array_columns)) {
              $is_uptodate = false;
          } else {
              foreach ($array_columns as $ident => $column) {
                  #printf("Required column: %s.%s".NL, $table, $column);
                  if (!in_array($column, $columns)) {
                      $is_uptodate = false;
                  }
              }
          }

      }


      return $is_uptodate;
  }

  public function getTableColumns()
  {
      $datamap = $this->_DataMap;
      $tables = array();

      foreach ($datamap as $key => $attributes) {
          if (Validator::is($attributes, 'array') && !preg_match('/^_/', $key)) {

              $table = $attributes['_table'];
                $tables[$table] = array();
              foreach (array('_unique', '_locale', '_key', '_value') as $special_field) {
                  if (isset($attributes[$special_field]) && strlen($attributes[$special_field]) > 0) {
                      $tables[$table][($attributes[$special_field])] = $attributes[$special_field];
                  }
              }

              if ($attributes['_type'] == 'all-in-a-row') {

                  foreach ($attributes['_mapping'] as $map => $field) {
                      $tables[$table][($field)] = $field;
                  }

              }

              if ($attributes['_type'] == 'key-value') {

              }

          }
      }

      return $tables;
  }

  public function getSQL($override=false)
  {

      $datamap = $this->_DataMap;
      $hexuid  = $datamap['_usehexuid'];

      $tables = $this->getTableColumns();

      $return1 = array();
      $return2 = array();
      foreach ($tables as $table => $fields) {
          if (!$table || strlen($table) < 1) continue;

          $delete = (($override == true) ? "DROP TABLE IF EXISTS `$table`;" : '');

          $temp = "--\n-- Structure for table `$table`\n--\n\n";

            if ($delete) $return2[] = $delete."\n";
          $temp .= "CREATE TABLE IF NOT EXISTS `$table` ( \n";

          $fields_sql = array();
          foreach ($fields as $field) {
              $datatype = false;

              if (isset($datamap['_datatypes'][$field])) {
                  if (substr($datamap['_datatypes'][$field], 0, 1) == ':') {
                      if (preg_match('/^:([A-Za-z]+):(null|):(.+)$/', $datamap['_datatypes'][$field], $matches)) {
                          $datatype = sprintf($this->predefined_datatypes[($matches[1])], (($matches[2] == 'null') ? 'NULL' : 'NOT NULL'), (($matches[3] == 'null') ? 'NULL' : '\''.$matches[3].'\''));
                      }
                  } else {
                      $datatype = $datamap['_datatypes'][$field];
                  }
              } else {

                  foreach ($this->predefined_fields as $dfield => $dtype) {
                      if ($dfield == $field || preg_match('/_'.$dfield.'$/', $field)) {
                          $datatype = $dtype;
                      }
                  }

                  if (!$datatype) {
                      $datatype = $this->default_datatype;
                  }

              }


              $fields_sql[] = "`$field` $datatype";


          }
          foreach ($fields as $field) {

              foreach ($this->predefined_indexes as $ident => $index) {
                  if ($ident == $field) {
                      $indexes_sql[$ident] = $index['_type']." ".uniqid('index_')." (`".join("`, `", $index['_fields'])."`)";
                  }
              }

              if (isset($datamap['_indexes'])) {
                  foreach ($datamap['_indexes'] as $ident => $index) {
                      if ($ident == $field) {
                          $indexes_sql[$ident] = $index['_type']." ".uniqid('index_')." (`".join("`, `", $index['_fields'])."`)";
                      }
                     }
              }

              if (preg_match('/_uid$/', $field) && !isset($indexes_sql[$field])) {
                  $indexes_sql[$field] = "KEY ".uniqid('index_')." (`".$field."`)";
              }
             }


          $fields_sql = array_merge($fields_sql, $indexes_sql);
          $indexes_sql = array();

          $temp_sql = $fields_sql;
          $fields_sql = array();
          foreach ($temp_sql as $row) {
              $fields_sql[] = "    ".$row;
          }

          $temp .= join(",\n", $fields_sql)."\n";

          $temp .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8;\n";

          $return1[] = $temp;
      }


      #var_dump(array(0 => $return1, 1 => $return2));
      return array(0 => $return1, 1 => $return2);
      #return $return;
  }

  public function copy($clearFields=false)
  {

    $array = $this->getAllAttributes();

    if ($clearFields !== false && Validator::is($clearFields, 'array')) {
      foreach ($clearFields as $field) {
        if (isset($array[$field])) unset($array[$field]);
      }
    }

    if (isset($array['uid'])) unset($array['uid']);

    $object = get_class($this);
    $newObject = new $object();
    $newObject->update($array);

    return $newObject;
  }

  public function getCurrentLanguage()
  {
      return VFactory::getLanguage();
      /*if (VInstance::f('DataLanguage') && VInstance::f('DataLanguage')->isValid()) {
          return VInstance::f('DataLanguage');
      }
      if (VInstance::f('Language') && VInstance::f('Language')->isValid()) {
          return VInstance::f('Language');
      }

      throw new Exception("Could not determine language");*/
  }

  public function hasField($__field)
  {

      foreach ($this->_DataMap as $key => $attributes) {
          if (preg_match('/^_/', $key)) continue;

          if (in_array($__field, $attributes['_mapping'])) {
              return true;
          }

          if (isset($attributes['_unique']) && $attributes['_unique'] == $__field) {
              return true;
          }

          if (isset($attributes['_locale']) && $attributes['_unique'] == $__field) {
              return true;
          }

      }

      return false;
  }

  protected function createNewAtDatabase()
  {
    $this->objDB->setTable($this->_DataMap['_head'], true);

    if ($this->usingHexUid === true) {
      $uid = uniqid();
      $this->objDB->insertRow("`".$this->_DataMap['_uid']."`", "'".$uid."'");
    } else {
      $this->objDB->insertRow("", "");
      $uid = $this->objDB->getLastInsertID();

      if (!is_int($uid)) {
        return false;
      }
    }

    $this->setUID($uid);
    return true;
  }

  protected function updateRelation($param, $what, $bOnCreate)
  {
    $dbLayout = isset($this->_DataMap[$what]) ? $this->_DataMap[$what] : false;

    if (!$dbLayout || isset($dbLayout['_ignore'])) {
        return null;
    }

    $param = $this->parseSpecials($param, $dbLayout, $bOnCreate);

    switch ($dbLayout['_type']) {
      case "key-value":
        $this->updateRelationKeyValue($param, $dbLayout);
        break;

      case "all-in-a-row":
        $this->updateRelationAllInARow($param, $dbLayout);
        break;

      default:
        throw new Exception("Property '_type' in Relation '$what' is Missing in Object '".get_class($this)."'.\n");
        break;
    }
  }

  /**
   * Aendert einen Datensatz
   *
   * @return array
   */
  protected function updateRelationKeyValue($param, $dbLayout)
   {
    if ( !is_object($this->objDB) ) {
      throw new Exceptoin("\$this->objDB is not a Valid 'VDatabase' instance.");
    }

    $arrFieldList = array();
    $fieldKey     = $dbLayout['_key'];
    $fieldValue   = $dbLayout['_value'];
    $fieldUnique  = $dbLayout['_unique'];

    /*
     * Setting Table
     */
    $this->objDB->setTable($dbLayout['_table'], true);

    foreach ($dbLayout['_mapping'] as $src => $dst) {
      if (isset($param[$src])) {
        $arrFieldList[$dst] = $param[$src];
      }
    }

    foreach ($arrFieldList as $key => $value) {
      $upateSuccessful = false;
      if ( !$this->isValid() ) {
        return false;
      }
      if ( $this->isValid() ) {
        $this->objDB->updateRow("`$fieldValue` = '".$this->objDB->escape(stripslashes($value))."'", "`$fieldUnique` = '".$this->getUID()."' AND `$fieldKey` = '$key'");
        $upateSuccessful = (bool)$this->objDB->getNumRows();
      }
      if ( !$upateSuccessful && !$this->rowExists("`$fieldUnique` = '".$this->getUID()."' AND `$fieldKey` = '$key'") ) {
        $this->objDB->insertRow("`$fieldUnique`, `$fieldKey`, `$fieldValue`", "'".$this->getUID()."', '".$this->objDB->escape(stripslashes($key))."', '".$this->objDB->escape(stripslashes($value))."'");
      }
    }

    return true;
  }


  protected function updateRelationAllInARow($param, $dbLayout)
  {
    if ( !is_object($this->objDB) ) {
      throw new Exception("\$this->objDB is not a Valid 'VDatabase' instance.");
    }

    #print "fooo";exit;

    $arrFieldList = array();
    $sFields      = "";
    $sValues      = "";
    $sUpdate      = "";
    $fieldUnique  = $dbLayout['_unique'];
    $where_language = "";

      if (isset($dbLayout['_locale'])) {
        $where_language .= " AND `".$dbLayout['_locale']."` = '".$this->getCurrentLanguage()->uid."'";
        $sFields = "`".$dbLayout['_locale']."`";
        $sValues = "'".$this->objDB->escape(stripslashes($this->getCurrentLanguage()->uid))."'";
    }

    /*
     * Setting Table
     */
    $this->objDB->setTable($dbLayout['_table'], true);

    foreach ($dbLayout['_mapping'] as $src => $dst) {
      if (isset($param[$src])) {

        $spacer = ($sFields == "") ? "" : ", ";
        $sFields .= $spacer."`$dst`";
        $sValues .= $spacer."'".$this->objDB->escape(stripslashes($param[$src]))."'";
        $spacer = ($sUpdate == "") ? "" : ", ";
        $sUpdate .= $spacer."`$dst` = '".$this->objDB->escape(stripslashes($param[$src]))."'";
      }
    }

    if ( $this->isValid() ) {
      $this->objDB->updateRow($sUpdate, "`$fieldUnique` = '".$this->getUID()."'".$where_language);
      $upateSuccessful = (bool)$this->objDB->getNumRows();
    }
    if ( !$upateSuccessful && !$this->rowExists("`$fieldUnique` = '".$this->getUID()."'".$where_language) ) {
      $this->objDB->insertRow("`$fieldUnique`, ".$sFields, "'".$this->getUID()."', ".$sValues);
    }

    return true;
  }

  public function delete()
  {
    if (!$this->isValid()) {
      return false;
    }

    $this->openDatabase();
    if ( !$this->objDB->startTransaction() ) {
      die("startTransaction failed! (".$this->objDB->strError.")<br />");
      return false;
    }

    foreach ($this->_DataMap as $key => $value) {
      if (!preg_match('/^_/', $key)) {
        $this->deleteRelation($key);
      }
    }

    /*
     * Transaction end
     */
    if ( !$this->objDB->commitTransaction() ) {
      die("commitTransaction failed! (".$this->objDB->intError." - ".$this->objDB->strError.")<br />");
      return false;
    }

    $this->closeDatabase();

    return true;
  }

  protected function deleteRelation($what)
  {
    $dbLayout = isset($this->_DataMap[$what]) ? $this->_DataMap[$what] : false;

      if (!$dbLayout || isset($dbLayout['_ignore'])) {
        return null;
    }

    switch ($dbLayout['_type']) {
      case "key-value":
        $this->deleteRelationKeyValue($dbLayout);
        break;

      case "all-in-a-row":
        $this->deleteRelationAllInARow($dbLayout);
        break;

      default:
        throw new Exception("Property '_type' in Relation '$what' is Missing in Object '".get_class($this)."'.\n");
        break;
    }
  }

  protected function deleteRelationKeyValue($dbLayout)
    {
    $this->objDB->setTable($dbLayout['_table'], true);
    return $this->objDB->deleteRow("`".$dbLayout['_unique']."` = '".$this->getUID()."'");
  }

  protected function deleteRelationAllInARow($dbLayout)
  {
    $this->objDB->setTable($dbLayout['_table'], true);
    return $this->objDB->deleteRow("`".$dbLayout['_unique']."` = '".$this->getUID()."'");
  }

  private function parseSpecials($param, $dbLayout, $bOnCreate)
  {
    if (!isset($dbLayout['_specials'])) {
      return $param;
    }
    if (!Validator::is($dbLayout['_specials'], 'array')) {
      return $param;
    }
    foreach ($dbLayout['_specials'] as $field => $tmp) {
      list($criterium, $action) = explode(':', $tmp);
      $doAction = false;

      // oncreate onupdate
      if ($criterium == 'oncreate' && $bOnCreate == true) {
        $doAction = true;
      } elseif ($criterium == 'onupdate' && $bOnCreate == false) {
        $doAction = true;
      }

      if ($doAction) {
        $func_name = 'SpecialAction_'.$action;
        $param[$field] = $this->$func_name();
      }
    }

    return $param;
  }

  private function SpecialAction_unixtimestamp()
  {
    return time();
  }

  private function SpecialAction_mysqltimestamp()
  {
    return date("Y-m-d H:i:s");
  }

  protected function rowExists($where)
  {
    $this->objDB->selectRows("*", $where);
    return (bool)$this->objDB->getNumRows();
  }

  public function reloadAttributes()
  {
    $this->clearAttributes();
    $this->loadAttributesByUID($this->uid);
  }

  protected function loadAttributesByUID($uid)
  {
    foreach (array_keys($this->_DataMap) as $key) {
      if ( !preg_match('/^_/', $key) ) {
        $this->importDatabase($uid, $key);
      }
    }

    if (isset($this->_DataMap['_safemode']) && $this->_DataMap['_safemode'] == true) {
      $isSafe = $this->runSafeMode();

      if (!$isSafe) {
        #die("Safemode");
        $this->clearAttributes();
        $this->uid = false;
      }
    }
  }

  protected function runSafeMode()
      {
    if ($this->getAttribute('user_uid')) {
      $refUserManager = new UserManager();
      return $refUserManager->safeMode($this->uid);
    }
    if ($this->getAttribute('customer_uid')) {
      $refCustomerManager = new CustomerManager();
      return $refCustomerManager->safeMode($this->uid);
    }
    return true;
  }

  protected function importDatabase($uid, $database)
  {
    if ( !isset($this->_DataMap[$database]) ) {
      throw new Exception("_DataMap['".$database."'] is missing.");
    }

      if (!$this->_DataMap[$database] || isset($this->_DataMap[$database]['_ignore'])) {
          return null;
    }

    switch ($this->_DataMap[$database]['_type']) {
      case "key-value":
        $this->importDatabaseKeyValue($uid, $database);
        break;

      case "all-in-a-row":
        $this->importDatabaseAllInARow($uid, $database);
        break;

      default:
        throw new Exception("Database-Type '".$this->_DataMap[$database]['_type']."' is not defined by the Import.");
        break;
    }

    return true;
  }

  private function importDatabaseAllInARow($uid, $database)
  {
    $dbLayout = $this->_DataMap[$database];
    $select   = "";
    $where    = "`".$dbLayout['_unique']."` = '".$uid."'";

    if (isset($dbLayout['_locale'])) {
        $where .= " AND `".$dbLayout['_locale']."` = '".$this->getCurrentLanguage()->uid."'";;
    }

    foreach ($dbLayout['_mapping'] as $ident => $dbfield) {
      $spacer  = ($select == "") ? "" : ", ";
      $select .= $spacer."`$dbfield` AS `$ident`";
    }

    $this->openDatabase();
    $this->objDB->setTable($dbLayout['_table'], true);
    $this->objDB->selectRows($select, $where);
    if ( !$this->objDB->getNumRows() ) {
      return false;
        #throw new Exception("No Data found by Query: SELECT $select FROM ".$dbLayout['_table']." WHERE $where");
    }
    $this->objDB->nextRecord();
    $values = $this->objDB->getRecord();
    $this->closeDatabase();

    $this->importAttributes($values, $database);

    return true;
  }

  private function importDatabaseKeyValue($uid, $database)
  {
    $dbLayout = $this->_DataMap[$database];
    $select   = "`".$dbLayout['_key']."`, `".$dbLayout['_value']."`";
    $where    = "`".$dbLayout['_unique']."` = '".$uid."'";

      if (isset($dbLayout['_locale'])) {
        $where .= " AND `".$dbLayout['_locale']."` = '".$this->getCurrentLanguage()->uid."'";;
    }

    $this->openDatabase();
    $this->objDB->setTable($dbLayout['_table'], true);
    $this->objDB->selectRows($select, $where);
    if ( !$this->objDB->getNumRows() ) {
      #throw new Exception("No Data found by Query: SELECT $select FROM ".$dbLayout['_table']." WHERE $where");
      return null;
    }
    while ($this->objDB->nextRecord()) {
      $record = $this->objDB->getRecord();
      $this->setAttribute($record[($dbLayout['_key'])], $record[($dbLayout['_value'])], $database);
    }
    $this->closeDatabase();

    return true;
  }

  private function getReverseMapping($field, $database='default')
  {
    if ( !isset($this->_DataMap[$database]) ) {
      throw new Exception("_DataMap['".$database."'] is missing.");
    }
    if ( !isset($this->_DataMap[$database]['_mapping']) ) {
      throw new Exception("_DataMap['".$database."'] is missing.");
    }
  }

  protected function setUID($uid)
    {
    if ($this->usingHexUid === false && !Validator::is($uid, 'uid')) {
      return false;
    }
    if ($this->usingHexUid === true && !Validator::is($uid, 'hexuid')) {
      return false;
    }

    $this->uid = $uid;
    $this->loadAttributesByUID($this->uid);

    return true;
  }

  protected function openDatabase()
  {
    /*if (!Instance::f('db_'.$this->_DataMap['_database'])) {
      $this->objDB = new DBTransaction($this->_DataMap['_database']);
    } else {
      $this->objDB = &Instance::f('db_'.$this->_DataMap['_database']);
    }*/

    $this->objDB = VFactory::getDatabase($this->_DataMap['_database']);
    $this->objDB->printOutput = true;
    return true;
  }

  protected function closeDatabase()
  {
    if (is_object($this->objDB)) {
      unset($this->objDB);
    }
    return true;
  }
}
?>