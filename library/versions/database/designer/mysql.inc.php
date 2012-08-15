<?php

class VDatabaseDesignerMysql extends VDatabaseDesigner {
	
	public function isInstalled($model) {
  	$is_installed = true;
  	
  	$dbo =& VFactory::getDatabase();
  	$tables_installed = $dbo->getListOfTables();
  	
  	if (!in_array($this->getTableName(get_class($model)), $tables_installed)) {
  		$is_installed = false;
  	}
  	
  	return $is_installed;
	}
	
  
	public function isUpToDate($model) {
		$dbo =& VFactory::getDatabase();
		
  	$columns = $model->getFields();
  	foreach ($columns as $k => $column) {
  		$declaration = $model->getFieldDeclaration($column);
  		$columns[$k] = $declaration->get('db_column');
  	}
  	
  	$columns_installed = $dbo->getListOfColumns($this->getTableName(get_class($model)));
  	$is_uptodate = true;
  	
  	$dbo =& VFactory::getDatabase();
  	#$tables_installed = $dbo->getListOfTables();
  	
  	if (count($columns) != count($columns_installed)) {
  		$is_uptodate = false;
  	} else {
  	
	  	foreach ($columns as $column) {
		 		if (!in_array($column, $columns_installed)) {
		 			// TODO set debug message
		 			print $column." not in (".implode(", ", $columns_installed).")\n";
		 			$is_uptodate = false;
		 		}
	  	}
  	}
  	
  	
  	return $is_uptodate;
  }
	
  public function getDropTable($model) {
  	$table = $this->getTableName(get_class($model));
  	return array(sprintf("DROP TABLE IF EXISTS `%s`;", $table));
  }
  
	public function getCreateTable($model) {
		
		$table = $this->getTableName(get_class($model));
		#printf("Table name: %s".NL, $table);
		
		$sql = sprintf("CREATE TABLE IF NOT EXISTS `%s` \n", $table);
		
		$parts = array();
		foreach ($model->getFields() as $field) {
			$parts[] = $this->getColumnDeclaration($model, $field);
		}
		$sql .= sprintf("(\n%s\n)", implode(", \n", $parts));
		
		$sql .= sprintf(" ENGINE=MyISAM DEFAULT CHARSET=utf8;\n");
		
		return array($sql);
	}
	
	public function getCreateIndex($model) {
		
		$sql = array();
		
		foreach ($model->getFields() as $field) {
			$index = $this->getColumnIndex($model, $field);
			if ($index !== false) {
				$sql[] = $index;
			}
		}
		
		return $sql;
	}
	
	public function saveModel($model) {
		if (!is_object($model) || !is_subclass_of(get_class($model), 'VModelStructure')) {
			throw new Exception( sprintf("Model %s (Type: %s) is not a valid Object or parent of VModelStructure", get_class($model), get_type($model)) );
		}
		
		$dbo =& VFactory::getDatabase();
		
		$sql = $this->getUpdateFields($model,  ((!$model->isValid()) ? 'insert' : 'update'));
		
		if ($dbo->userQuery($sql))
			$model->isValid(true);
		
		return $model->isValid();
	}
	
	public function getModel(&$model, $filter=array()) {
		$model_name = get_class($model);
		
		$dbo =& VFactory::getDatabase();
		
		$dbo->setTable($this->getTableName($model_name));
		$dbo->selectRows("*", $this->prepareFilter($model, $filter), "none", "1");
		if (!$dbo->getNumRows()) {
			return $model; // or whatever
		}
		$dbo->nextRecord();
		$model->bulkSet($dbo->getRecord());
		$model->isValid(true);
		return $model;
	}
	
	public function getModels($model, $filter=array()) {
		$model_name = get_class($model);
		
		$dbo =& VFactory::getDatabase();
		
		$dbo->setTable($this->getTableName($model_name));
		$dbo->selectRows("*", $this->prepareFilter($model, $filter));
		if (!$dbo->getNumRows()) {
			return $model; // or whatever
		}
		
		$return = array();
		$i = 0;
		while ($dbo->nextRecord()) {
			$return[$i] = new $model_name();
			$return[$i]->bulkSet($dbo->getRecord());
			$return[$i]->isValid(true);
			$i++;
		}
		
		$model->isValid(true);
		return $return;
	}
	
	public function prepareFilter($model, $filter) {
		$dbo		=& VFactory::getDatabase();
		$fields = $model->getFields();
		
		foreach ($fields as $k => $field) {
  		$declaration =& $model->getFieldDeclaration($field);
			$column = $declaration->get('db_column');
  		$columns[$column] = $model->get($field);
  		if (isset($filter[$field])) {
  			$where[$column] = sprintf("'%s'", $dbo->escape($filter[$field]));
  		}
  		if ($declaration->get('primary_key', false) == true && isset($filter['pk'])) {
  			$where[$column] = sprintf("'%s'", $dbo->escape($filter['pk']));
  		}
  	}
  	
  	$sql = "";
  	foreach ($where as $column => $value) {
  		$sql .= sprintf("`%s` = %s", $column, $value);
  	}
  	return (($sql) ? $sql : "none");
	}
	
	public function getUpdateFields($model, $mode='update') {
		$dbo					=& VFactory::getDatabase();
		$fields 			= $model->getFields();
		$columns			= array();
		$declarations = array();
		$pkeys        = array();
		
  	foreach ($fields as $k => $field) {
  		$column = $model->getFieldDeclaration($field)->get('db_column');
  		$declarations[$column] =& $model->getFieldDeclaration($field);
  		$columns[$column] = (($mode == 'update') ? $declarations[$column]->onUpdate($model->get($field)) : $declarations[$column]->onCreate($model->get($field)) );
  		if ($declarations[$column]->get('primary_key', false) == true) {
  			$pkeys[$column] = $columns[$column];
  		}
  	}
  	
  	$sql = sprintf("%s %s SET \n", (($mode == 'update') ? "UPDATE" : "INSERT INTO"), $this->getTableName(get_class($model)));
  	
  	$keyvalues = array();
  	foreach ($columns as $column => $value) {
  		#print $value.NL;
  		if (is_null($value)) {
  			$value = 'NULL';
  		} elseif (is_bool($value)) {
  			$value = sprintf("'%s'", $dbo->escape((($value === false) ? 0 : 1)));
  		} else {
  			$value = sprintf("'%s'", $dbo->escape($value));
  		}
  		
  		$keyvalues[] = sprintf("\t`%s` = %s", $column, $value);
  		
  	}
  	
  	$sql .= implode(", \n", $keyvalues);
  	
  	if ($mode == 'update') {
  		
  		$keyvalues = array();
  		foreach ($pkeys as $column => $value) {
  			$keyvalues[] = sprintf("\t`%s` = '%s'\n", $column, $dbo->escape($value));
  		}
  		$sql .= sprintf("WHERE\n %s", implode(" AND ", $keyvalues));
  	}
  	
  	return $sql;
	}
	
	public function getColumnDeclaration($model, $field) {
		
		$field_declaration = $model->getFieldDeclaration($field);
		
		$declaration = sprintf("\t`%s` ", $field_declaration->get('db_column'));
		
		switch($field_declaration->get('type')) {
			case "string":
				if ($field_declaration->get('max_length') <= 64):
					$declaration .= sprintf("CHAR(%d) ", $field_declaration->get('max_length'));
				elseif ($field_declaration->get('max_length') <= 255):
					$declaration .= sprintf("VARCHAR(%d) ", $field_declaration->get('max_length'));
				elseif ($field_declaration->get('max_length') <= 65535):
					$declaration .= sprintf("TEXT ");
				elseif ($field_declaration->get('max_length') <= 16777215):
					$declaration .= sprintf("MEDIUMTEXT ");
				elseif ($field_declaration->get('max_length') <= 4294967295):
					$declaration .= sprintf("LONGTEXT ");
				endif;
				$declaration .= (($field_declaration->get('null') == true) ? "NULL " : "NOT NULL ");
				$declaration .= (($field_declaration->get('null') == true && is_null($field_declaration->get('default'))) ? "DEFAULT NULL" : (($field_declaration->get('default') != false) ? sprintf("DEFAULT '%s'", $field_declaration->get('default')) : "DEFAULT ''"));
				break;
			case "integer":
				//$declaration .= sprintf("INT(%d) ", $field_declaration->get('max_length'));
				if ($field_declaration->get('unsigned') == false) {
					if ($field_declaration->get('min_value') >= -128 && $field_declaration->get('max_value') <= 127):
						$declaration .= sprintf("TINYINT(%d) ", strlen($field_declaration->get('max_value'))-1);
					elseif ($field_declaration->get('min_value') >= -32768 && $field_declaration->get('max_value') <= 32767):
						$declaration .= sprintf("SMALLINT(%d) ", strlen($field_declaration->get('max_value'))-1);
					elseif ($field_declaration->get('min_value') >= -8388608 && $field_declaration->get('max_value') <= 8388607):
						$declaration .= sprintf("MEDIUMINT(%d) ", strlen($field_declaration->get('max_value'))-1);
					elseif ($field_declaration->get('min_value') >= -2147483648 && $field_declaration->get('max_value') <= 2147483647):
						$declaration .= sprintf("INT(%d) ", strlen($field_declaration->get('max_value'))-1);
					elseif ($field_declaration->get('min_value') >= -9223372036854775808 && $field_declaration->get('max_value') <= 9223372036854775807):
						$declaration .= sprintf("BIGINT(%d) ", strlen($field_declaration->get('max_value'))-1);
					endif;
				} else {
					if ($field_declaration->get('max_value') <= 255):
						$declaration .= sprintf("TINYINT(%d) UNSIGNED ", strlen($field_declaration->get('max_value'))-1);
					elseif ($field_declaration->get('max_value') <= 65535):
						$declaration .= sprintf("SMALLINT(%d) UNSIGNED ", strlen($field_declaration->get('max_value'))-1);
					elseif ($field_declaration->get('max_value') <= 16777215):
						$declaration .= sprintf("MEDIUMINT(%d) UNSIGNED ", strlen($field_declaration->get('max_value'))-1);
					elseif ($field_declaration->get('max_value') <= 4294967295):
						$declaration .= sprintf("INT(%d) UNSIGNED ", strlen($field_declaration->get('max_value'))-1);
					elseif ($field_declaration->get('max_value') <= 18446744073709551615):
						$declaration .= sprintf("BIGINT(%d) UNSIGNED ", strlen($field_declaration->get('max_value'))-1);
					endif;
				}
				$declaration .= (($field_declaration->get('zerofill') == true) ? "ZEROFILL " : "");
				$declaration .= (($field_declaration->get('null') == true) ? "NULL " : "NOT NULL ");
				$declaration .= (($field_declaration->get('null') == true && is_null($field_declaration->get('default'))) ? "DEFAULT NULL" : (($field_declaration->get('default') != false) ? sprintf("DEFAULT '%s'", $field_declaration->get('default')) : "DEFAULT '0'"));
				break;
			case "float":
				$declaration .= sprintf("DECIMAL(%d, %d) ", $field_declaration->get('decimal_places'), $field_declaration->get('max_digits'));
				$declaration .= (($field_declaration->get('unsigned') == true) ? "UNSIGNED " : "");
				$declaration .= (($field_declaration->get('zerofill') == true) ? "ZEROFILL " : "");
				$declaration .= (($field_declaration->get('null') == true) ? "NULL " : "NOT NULL ");
				$declaration .= (($field_declaration->get('null') == true && is_null($field_declaration->get('default'))) ? "DEFAULT NULL" : (($field_declaration->get('default') != false) ? sprintf("DEFAULT '%s'", $field_declaration->get('default')) : "DEFAULT '0.0'"));
				break;
			case "boolean":
				$declaration .= sprintf("BOOLEAN ");
				break;
			default:
				print "unknown type: ".$field_declaration->get('type');#
				break;
			
		}
		
		return $declaration;
	}
	
	public function getColumnIndex($model, $field) {
		$field_declaration = $model->getFieldDeclaration($field);
		
		if ($field_declaration->get('db_index') == true) {
			return sprintf(
				"ALTER TABLE `%s` ADD %sINDEX %s (`%s`);",
				$this->getTableName(get_class($model)),
				(($field_declaration->get('unique') == true) ? "UNIQUE " : ""),
				sprintf("%s_key", $field),
				$field
			);
		}
		if ($field_declaration->get('primary_key') == true) {
			return sprintf(
				"ALTER TABLE `%s` ADD PRIMARY KEY %s (`%s`);",
				$this->getTableName(get_class($model)),
				sprintf("%s_pk", $field),
				$field
			);
		}
		return false;
	}
	
	public function getTableName($model_name) {
		
		$parts = VString::splitCamelCase($model_name);
		$parts = VArray::strip_empty_values($parts);
		
		foreach ($parts as $k => $part) {
			if (strtolower($part) == 'component') {
				unset($parts[$k]);
			}
			if (strtolower($part) == 'model') {
				unset($parts[$k]);
			}
		}
		
		return VString::strtolower(implode('_', $parts));
	}
}