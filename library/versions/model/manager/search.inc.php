<?php

/**
 * 
 * Enter description here ...
 * @author marc
 *
 */
class VModelManagerSearch extends VModelManagerDefault {
	
	var $search_keywords = array();
  
  public function getAll() {
  	if (!is_object($this->object)) {
  		throw new Exception("Required object but ".gettype($this->object)." received.");
  	}
  	if (count($this->getKeywords()) > 0) {
			return $this->getAll_Search();
  	} else {
			return $this->getAll_Default();
  	}
  }
  
  public function getAll_Default() {
  	$dbo =& VFactory::getDatabase();
  	$dbo->setTable( sprintf("`%s`", $this->object->_DataMap['_head']) );
    $dbo->selectRows( 
    	sprintf("`%s`", $this->object->_DataMap['_uid']),
    	$this->getWhereCondition(), 
    	$this->getOrdering(), 
    	$this->getLimitCondition()
    );
    if (!$dbo->getNumRows()) {
      return array();
    }
    
    $record = array();
    while ($dbo->nextRecord()) {
      $record[] = $dbo->getRecord();
    }
    
    return $this->getObjects($record, $this->object_name);
  }
  
	public function getAll_Search() {
  	$add = $this->getQuery();
  	$dbo =& VFactory::getDatabase();
  	$dbo->setTable( $add['from'] );
    $dbo->selectRows( 
    	sprintf("DISTINCT(`%s`)", $this->object->_DataMap['_uid']), 
    	$this->getWhereCondition($add['where']), 
    	$this->getOrdering(), 
    	$this->getLimitCondition()
    );
    
    if (!$dbo->getNumRows()) {
      return array();
    }
    
    $record = array();
    while ($dbo->nextRecord()) {
      $record[] = $dbo->getRecord();
    }
    
    return $this->getObjects($record, $this->object_name);
  }
  
	public function getNumRows() {
		if (!is_object($this->object)) {
  		throw new Exception("Required object but ".gettype($this->object)." received.");
  	}
		if (count($this->getKeywords()) > 0) {
			return $this->getNumRows_Search();
  	} else {
			return $this->getNumRows_Default();
  	}
  }
  
	public function getNumRows_Default() {
  	$dbo =& VFactory::getDatabase();
  	$dbo->setTable( sprintf("`%s`", $this->object->_DataMap['_head']) );
    $dbo->selectRows("COUNT(*) as `counter`", $this->getWhereCondition());
    $dbo->nextRecord();
    return $dbo->f('counter');
  }
  
	public function getNumRows_Search() {
  	$add = $this->getQuery();
		$dbo =& VFactory::getDatabase();
  	$dbo->setTable( $add['from'] );
    $dbo->selectRows( sprintf("COUNT(DISTINCT(`%s`)) as `counter`", $this->object->_DataMap['_uid']), $this->getWhereCondition($add['where']));
    $dbo->nextRecord();
    return $dbo->f('counter');
  }
  
  public function getKeywords() {
  	return $this->search_keywords;
  }
  
  public function setKeyword($__keyword) {
  	if (!in_array($__keyword, $this->search_keywords)) {
  		$this->search_keywords[] = $__keyword;
  	}
  }
  
  public function clearKeywords() {
  	$this->search_keywords = array();
  }
	
  private function getQuery() {
  	$data_map = $this->object->_DataMap;
  	
  	$query   = array( 'from' => "", 'where' => "");
  	$tables  = array();
  	$fields  = array();
  	$uniques = array();
  	
  	foreach ($data_map as $tbl_ident => $attributes) {
  		if (preg_match('/^_/', $tbl_ident)) continue;
  		
  		$tables[$tbl_ident]  = $attributes['_table'];
  		$uniques[$tbl_ident] = $attributes['_unique'];
  		
  		
  		
  		if ($attributes['_type'] == 'all-in-a-row') {
  			
  			foreach ($attributes['_mapping'] as $tbl_key => $table) {
  				if (preg_match('/uid$/', $table)) continue; 		// skip 'uid'
  				if (preg_match('/_uid$/', $table)) continue; 		// skip '*_uid'
  				if (preg_match('/^ts_/', $table)) continue; 		// skip 'ts_*'
  				if (preg_match('/^status$/', $table)) continue; // skip 'status'
  				
  				$fields[$tbl_ident][] = $table;
  			}
  			
  		}
  		
  		
  	}
  	
  	/*
  	 * Main Table
  	 */
  	$query['from'] .= " `".$tables['default']."` \n";

  	
  	/*
  	 * JOIN Additional Tables 
  	 */
  	$wheres = array();
  	foreach ($tables as $tbl_ident => $table) {
  		if ($tbl_ident == 'default') continue;
  		#$query .= " LEFT JOIN `$table` ON (`".$tables['default']."`.`".$uniques['default']."` = ``.``) \n";
  		$add = "";
  		if (isset($data_map[$tbl_ident]['_locale'])) {
  			$add = sprintf(" AND `%s` = '%s' ", $data_map[$tbl_ident]['_locale'], $this->object->getCurrentLanguage()->uid);
  		}
  		
  		$query['from'] .= sprintf(" LEFT JOIN `%s` ON (`%s`.`%s` = `%s`.`%s` %s) \n", $table, $tables['default'], $uniques['default'], $table, $uniques[$tbl_ident], $add);
  	}
  	
  	
  	
  	foreach ($this->getKeywords() as $keyword) {
	  	$wheres_temp = array();
	  	foreach ($tables as $tbl_ident => $table) {
	  		if (!isset($fields[$tbl_ident])) continue;
	  		foreach ($fields[$tbl_ident] as $field) {
	  			$wheres_temp[] = sprintf(" `%s`.`%s` LIKE '%%%s%%' \n", $tables[$tbl_ident], $field, $keyword);
	  		}
	  	}
	  	$wheres[] = $wheres_temp;
	  }
  	
  	foreach ($wheres as $where_keyword) {
  		$temp_where_kw[] = join(" OR ", $where_keyword);
  	}
  	
  	$where = sprintf(" ( (%s) )", join(") AND (", $temp_where_kw));
  	
  	$query['where'] = $where;
  	
  	return $query;
  	
  }
  
	
}