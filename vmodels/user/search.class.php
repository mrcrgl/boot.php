<?php
/**
 * DMT - Developer Moddelling Tool
 * Created on 24.07.2007
 *
 * @author Marc Riegel
 * @version 1.0
 * 
 * ---------------------------------
 * 
 * ---------------------------------
 * 
 */

class UserSearch extends UserManager {
  
  private $relevanzBonusByMatch = 5;
  
  private $searchAttributeList = array(
    'firstname',
    'lastname',
    'company_name',
    'customer_id',
    'email'
  );
  
  private $arrKeywords = array();
  
  private $boolSearchRequestDone = false;
  
  public function setKeyword($keyword) {
    if (!in_array($keyword, $this->arrKeywords) && strlen($keyword) > 1) {
      $this->arrKeywords[] = $keyword;
    }
  }
  
  public function clearKeywords() {
    $this->arrKeywords = array();
    
    if ($this->boolSearchRequestDone == true) {
      $query = "DROP TEMPORARY TABLE `search_request`";
      $dbo =& VFactory::getDatabase();
    	$dbo->userQuery($query);
    }
    
    $this->boolSearchRequestDone = false;
  }
  
  private function createTempTable() {
    $query = "CREATE TEMPORARY TABLE `search_request` (
                `uid` VARCHAR( 13 ) NOT NULL ,
                `ts_update` INT NULL ,
                `relevance` INT NULL ,
                 INDEX ( `uid` , `ts_update`, `relevance` )
              ) ENGINE = MYISAM ";
    $dbo->userQuery($query);
  }
  
  public function getAll() {
    if (count($this->arrKeywords)) {
      return $this->startRequest();
    }
    return parent::getAll();
  }
  
  public function getNumRows() {
    if (count($this->arrKeywords)) {
      return $this->requestNumRows();
    }
    return parent::getNumRows();
  }
  
  public function searchRequest() {
    if ($this->boolSearchRequestDone == true) {
      return true;
    }
    
    $this->createTempTable();
    
    $query = "INSERT INTO `search_request` (`uid`, `ts_update`, `relevance`) " .
             "(SELECT m.uid, m.ts_update, (" .
               "SELECT  SUM((mar.relevance".$this->getBonusBySearchMatch()." )) " .
               "FROM   `user_attributes` AS ma, `user_attribute_relevance` AS mar " .
               "WHERE  m.uid = ma.user_uid AND ma.attribute = mar.user_attribute AND ma.attribute IN (".$this->getWellformedAttributeList().") AND (".$this->getKeywordQuerySnippet("ma.value").")" .
             ") AS rel " .
             "FROM `user` AS m " .
             "WHERE 1 ORDER BY rel DESC LIMIT 500)";
    #print $query;
    $dbo =& VFactory::getDatabase();
    $dbo->userQuery($query);
    
    if (!$dbo->getNumRows()) {
      return false;
    }
    $this->boolSearchRequestDone = true;
    
    return true;
  }
  
  
  public function startRequest() {
    $this->searchRequest();
    
    $dbo =& VFactory::getDatabase();
    $dbo->userQuery("SELECT `uid`, `relevance` FROM `search_request` WHERE `relevance` IS NOT NULL ORDER BY `relevance` DESC, `ts_update` DESC LIMIT ".((isset($this->pagination)) ? $this->pagination->getLimitStatement() : "0,15"));
    
    $record = array();
    while ($dbo->nextRecord()) {
      $record[] = $dbo->getRecord();
    }
    
    return $this->getObjects($record, 'User');
  }
  
  private function requestNumRows() {
    $this->searchRequest();
    $dbo =& VFactory::getDatabase();
    $dbo->userQuery("SELECT COUNT(*) AS `count` FROM `search_request` WHERE `relevance` IS NOT NULL");
    $dbo->nextRecord();
    return $dbo->f("count");
  }
  
  private function getBonusBySearchMatch() {
    $bmList = "";
    foreach ($this->arrKeywords as $keyword) {
      $bmList .= " + IF(STRCMP(ma.value,'$keyword'),'0','".$this->relevanzBonusByMatch."')";
    }
    return $bmList;
  }
  
  private function getWellformedAttributeList() {
    $wfList = "";
    foreach ($this->searchAttributeList as $attribute) {
      $wfList .= (($wfList == "") ? "" : ", " )."'$attribute'";
    }
    return $wfList;
  }
  
  private function getKeywordQuerySnippet($field) {
    $kqSnippet = "";
    foreach ($this->arrKeywords as $keyword) {
      $kqSnippet .= (($kqSnippet == "") ? "" : " OR " )."$field LIKE '%$keyword%' ";
    }
    return $kqSnippet;
  }
  
}
?>