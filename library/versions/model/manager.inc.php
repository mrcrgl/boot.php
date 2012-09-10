<?php

class VModelManager extends VObject {

	/**
	 *
	 * The parent model
	 * @var object
	 */
	var $_model = null;

	/**
	 *
	 */
	var $_table = null;

	/**
	 *
	 * the given options
	 * @var array
	 */
	var $_options = array();

	//static $_instances = array();

	/**
	 * the __constructor
	 *
	 * @param 	object 		parent model
	 */
	public function __construct(&$model) {
		$this->_model =& $model;
		#printf("Manager initialized with model %s".NL, get_class($model));
		$designer =& VDatabaseDesigner::getInstance();
		$this->_table = $designer->getTableName(get_class($model));
		$this->_model_name = get_class($this->_model);
	}


	public static function getInstance(&$model, &$related=null, $type='basic') {
		/*
		if (!isset(self::$_instances[$model]) || !isset(self::$_instances[$model][$column])) {
			if (is_null($type)) {
				// TODO Set Debug message
				return false;
			}

			$classname = sprintf('VModelField%s', $type);
			VLoader::autoload($classname);

			if (!class_exists($classname)) {
				die( sprintf('Invalid VModelField type received: %s', $type) );
			}

			self::$_instances[$model][$column] = new $classname($options);
		}

		return self::$_instances[$model][$column];
		*/

		$classname = sprintf('VModelManager%s', VString::underscores_to_camelcase($type));
		VLoader::autoload($classname);

		if (!class_exists($classname)) {
			throw new Exception( sprintf('Invalid VModelField type received: %s', $type) );
		}

		return new $classname(&$model, &$related);
	}


	/* filtering || query functions */
	/**
	 * sets the options with prefix 'order_by'
	 * format:
	 * 	{field_name} : {direction}
	 *
	 * field_name 	represents an model-field
	 * direction 		represents ASC or DESC (default: ASC)
	 *
	 * @param 	array|vopts $options
	 * @return 	$this				for chaining
	 */
	public function order_by($options=array()) {
		$options = VArray::parseOptions($options);
		$this->importOptions(array('order_by'=> $options));

		return $this;
	}

	/**
	 * sets the options with prefix 'limit'
	 * valid options:
	 * 	count 	integer			the count of results
	 * 	offset	integer			fetch with offset
	 *
	 * @param 	array|vopts $options
	 * @return 	$this				for chaining
	 */
	public function limit($options=array()) {
		$options = VArray::parseOptions($options);
		$this->importOptions(array('limit'=> $options));

		return $this;
	}

	/**
	 * sets the options with prefix 'distinct'
	 *
	 * not implemented yet
	 *
	 * @param 	array|vopts $options
	 * @return 	$this				for chaining
	 */
	public function distinct($options=array()) {
		$options = VArray::parseOptions($options);
		$this->importOptions(array('distinct'=> $options));

		return $this;
	}

	/**
	 * sets the options with prefix 'exclude'
	 * given filter is declared with 'NOT' in the query
	 *
	 * format:
	 * 	{field_name}[__{lookup}] : {value}
	 *
	 * field_name represents an model-field
	 * lookup 		represents a optional parameter @see lookups
	 * value 			represents the value to filter for
	 *
	 * @param 	array|vopts $options
	 * @return 	$this				for chaining
	 */
	public function exclude($options=array()) {
		$options = VArray::parseOptions($options);
		$this->importOptions(array('exclude'=> $options));

		return $this;
	}

	/**
	 * sets the options with prefix 'filter'
	 *
	 * format:
	 * 	{field_name}[__{lookup}] : {value}
	 *
	 * field_name represents an model-field
	 * lookup 		represents a optional parameter @see lookups
	 * value 			represents the value to filter for
	 *
	 * @param 	array|vopts $options
	 * @return 	$this				for chaining
	 */
	public function filter($options=array()) {
		$options = VArray::parseOptions($options);
		$this->importOptions(array('filter'=> $options));

		return $this;
	}

	/* single model functions */

	public function clear() {

		return $this;
	}

	public function create($model=null) {

		return $this;
	}

	/**
	 * initialize the parent model
	 *
	 * @param 	array|vopts $options
	 * @return 	bool				true if successful, false otherwise
	 */
	public function get($options=array()) {
		$options = VArray::parseOptions($options);
		$this->importOptions($options);

		/*
		 * override limit to get at least one row
		 */
		$this->limit('[count:1,offset:0]');

		$dbo =& VFactory::getDatabase();
		$dbo->userQuery( $this->buildQuerySelect() );
		if (!$dbo->getNumRows()) {
			return false; // or whatever
		}
		$dbo->nextRecord();
		$this->_model->bulkSet($dbo->getRecord());
		$this->_model->isValid(true);

    $this->clearOptions();

		return true;
	}

	/**
	 * Function to check if a model exists
	 *
	 * @param 	array|vopts $options
	 * @return 	bool				true if exists, otherwise false
	 */
	public function exists($options=array()) {
		$options = VArray::parseOptions($options);
		$this->importOptions($options);

		/*
		 * override limit to get at least one row
		 */
		$this->limit('[count:1,offset:0]');

		$dbo =& VFactory::getDatabase();
		$dbo->userQuery( $this->buildQuerySelect() );

		$this->clearOptions();

		return (bool)$dbo->getNumRows();
	}

	/**
	 * get the result count
	 *
	 * @param 	array|vopts $options
	 * @return 	integer			number of results
	 */
	public function count($options=array()) {
		$options = VArray::parseOptions($options);
		$this->importOptions($options);

		$dbo =& VFactory::getDatabase();
		$dbo->userQuery( $this->buildQuerySelect() );

		$this->clearOptions();

		return $dbo->getNumRows();
	}

	/**
	 * get a list of matching models
	 *
	 * @param 	array|vopts $options
	 * @return 	array				models, matched by filter
	 */
	public function fetch($options=array()) {
		$options = VArray::parseOptions($options);
		$this->importOptions($options);

		$model_name = $this->_model_name; #get_class($this->_model);

		$dbo =& VFactory::getDatabase();
		#print $this->buildQuerySelect();
		$dbo->userQuery( $this->buildQuerySelect() );
		$this->clearOptions();

		if (!$dbo->getNumRows()) {
			return array(); // or whatever
		}

		$return = array();
		$i = 0;
		while ($dbo->nextRecord()) {
		  $return[$i] = new $model_name();
			$return[$i]->bulkSet($dbo->getRecord(), true);
			$return[$i]->isValid(true);
			$i++;
		}

		return $return;
	}

	/**
	 * delete matching models
	 *
	 * @param 	array|vopts $options
	 * @return 	array				models, matched by filter
	 */
	public function delete($options=array()) {
	  $options = VArray::parseOptions($options);
	  $this->importOptions($options);

	  $model_name = $this->_model_name; #get_class($this->_model);

	  $dbo =& VFactory::getDatabase();
	  #print $this->buildQuerySelect();exit;
	  $dbo->userQuery( $this->buildQueryDelete() );
	  $this->clearOptions();

	  return $dbo->getNumRows();
	}

	public function setTable($table) {
		$this->_table = $table;
	}

	public function setModelName($modelname) {
	  $this->_model_name = $modelname;
	}

	/**
	 * merge new options to existing
	 *
	 * @param 	array		 $options
	 * @return 	void
	 */
	private function importOptions($options=array()) {
		$this->_options = array_merge_recursive($this->_options, $options);
	}

	/**
	 * get options of specified type
	 *
	 * @param 	string		$type
	 * @return 	array			options of type
	 */
	private function getOptions($type) {
		return ((isset($this->_options[$type])) ? $this->_options[$type] : array());
	}

	/**
	 * clear options
	 *
	 * @return 	void
	 */
	protected function clearOptions() {
	  $this->_options = array();
	}

	/**
	 * build sql query by given options
	 *
	 * @return 	string		sql query for select statements
	 */
	private function buildQuerySelect() {

		$sql = sprintf(
			"SELECT %s FROM %s WHERE %s %s %s",
			$this->buildQueryFields(),
			$this->_table,
			$this->buildQueryWhere(),
			$this->buildQueryOrder(),
			$this->buildQueryLimit()
		);
		#print $sql.NL;
		return $sql;
	}

	/**
	 * build sql query by given options
	 *
	 * @return 	string		sql query for select statements
	 */
	private function buildQueryDelete() {

	  $sql = sprintf(
	      "DELETE FROM %s WHERE %s %s",
	      $this->_table,
	      $this->buildQueryWhere(),
	      $this->buildQueryLimit()
	  );
	  #print $sql.NL;
	  return $sql;
	}

	/**
	 * static function to define the select fields
	 *
	 * @todo		implementation of distinct values
	 * @return 	string			sql select clause
	 */
	private function buildQueryFields() {
		return "*";
	}

	/**
	 * function to combine filter and exclude options
	 *
	 * @return 	string			sql where clause
	 */
	private function buildQueryWhere() {

		$filter  = $this->getWhereConditions( $this->getOptions('filter') );
		$exclude = $this->getWhereConditions( $this->getOptions('exclude') );

		$part = ((count($filter)) ? implode(" AND ", $filter) : " 1 ");
		$part .= ((count($exclude)) ? sprintf(" AND NOT (%s)", implode(" AND ", $exclude)) : "");

		return $part;
	}

	/**
	 * function to generate the ordering by given options
	 *
	 * @return 	string			sql order clause
	 */
	private function buildQueryOrder() {
		$options = $this->getOptions('order_by');

		$fields = array();
		foreach ($options as $varname => $direction) {
			if (strtolower($direction) != 'asc' && strtolower($direction) != 'desc')
				$direction = 'asc';
			$fields[] = sprintf("`%s` %s", $this->getColumnByName($varname), ((strtolower($direction) == 'asc')) ? "ASC" : "DESC");
		}

		if (!count($fields))
			return "";
		return sprintf("ORDER BY %s", implode(", ", $fields));
	}

	/**
	 * function to generate the limits by given options
	 *
	 * @return 	string			sql limit clause
	 */
	private function buildQueryLimit() {
		$options = $this->getOptions('limit');

		if (!isset($options['count']))
			return '';

		return sprintf("LIMIT %d OFFSET %d", (int)$options['count'], (int)((isset($options['offset'])) ? $options['offset'] : 0));
	}

	/**
	 * function to convert filter and exclude options to sql
	 *
	 * @param		array			key-value set of field - value relations
	 * @return 	array			set of conditions
	 */
	private function getWhereConditions($options=array()) {
		$conditions = array();

		foreach ($options as $key => $value) {
			#print "$key -> $value".NL;
			$condition = "";

			if (preg_match('/^(?P<name>.+)__(?P<lookup>.+)$/', $key, $matches)) {
				#var_dump($matches);
				$varname = $matches['name'];
				$lookup  = $matches['lookup'];
			} else {
				$varname = $key;
				$lookup  = 'exact';
			}

			switch($lookup) {
				case "exact":
					$condition = sprintf(
						"`%s` = '%s'",
						$this->getColumnByName($varname),
						$this->prepareValue($value)
					);
					break;

				case "iexact":
					$condition = sprintf(
						"`%s` ILIKE '%s'",
						$this->getColumnByName($varname),
						$this->prepareValue($value)
					);
					break;

				case "contains":
					$condition = sprintf(
						"`%s` LIKE '%%%s%%'",
						$this->getColumnByName($varname),
						$this->prepareValue($value)
					);
					break;

				case "icontains":
					$condition = sprintf(
						"`%s` ILIKE '%%%s%%'",
						$this->getColumnByName($varname),
						$this->prepareValue($value)
					);
					break;

				case "in":
					if (!is_array($value)) throw new Exception(sprintf("Lookup '%s' needs an array as value", $lookup));
					$condition = sprintf(
						"`%s` IN ('%s')",
						$this->getColumnByName($varname),
						implode("', '", $this->prepareValue($value))
					);
					break;

				case "gt":
					$condition = sprintf(
						"`%s` > '%s'",
						$this->getColumnByName($varname),
						$this->prepareValue($value)
					);
					break;

				case "gte":
					$condition = sprintf(
						"`%s` >= '%s'",
						$this->getColumnByName($varname),
						$this->prepareValue($value)
					);
					break;

				case "lt":
					$condition = sprintf(
						"`%s` < '%s'",
						$this->getColumnByName($varname),
						$this->prepareValue($value)
					);
					break;

				case "lte":
					$condition = sprintf(
						"`%s` <= '%s'",
						$this->getColumnByName($varname),
						$this->prepareValue($value)
					);
					break;

				case "startswith":
					$condition = sprintf(
						"`%s` LIKE '%s%%'",
						$this->getColumnByName($varname),
						$this->prepareValue($value)
					);
					break;

				case "istartswith":
					$condition = sprintf(
						"`%s` ILIKE '%s%%'",
						$this->getColumnByName($varname),
						$this->prepareValue($value)
					);
					break;

				case "endswith":
					$condition = sprintf(
						"`%s` LIKE '%%%s'",
						$this->getColumnByName($varname),
						$this->prepareValue($value)
					);
					break;

				case "iendswith":
					$condition = sprintf(
						"`%s` ILIKE '%%%s'",
						$this->getColumnByName($varname),
						$this->prepareValue($value)
					);
					break;

				case "range":
					if (!is_array($value)) throw new Exception(sprintf("Lookup '%s' needs an array as value", $lookup));
					if (count($value) != 2) throw new Exception(sprintf("Lookup '%s' needs exactly to array values", $lookup));
					$condition = sprintf(
						"`%s` BETWEEN '%s' AND '%s'",
						$this->getColumnByName($varname),
						$this->prepareValue(array_shift($value)),
						$this->prepareValue(array_shift($value))
					);
					break;

				case "year":
					if (preg_match('/^[0-9]{4}$/', $value)) throw new Exception(sprintf("Lookup '%s' needs an 4 digit year value", $lookup));
					// TODO check field type
					$condition = sprintf(
						"`%s` BETWEEN '%d-01-01' AND '%d-12-31 23:59:59.99999'",
						$this->getColumnByName($varname),
						$this->prepareValue($value),
						$this->prepareValue($value)
					);
					break;

				case "month":
					if (preg_match('/^[0-9]{2}$/', $value)) throw new Exception(sprintf("Lookup '%s' needs an 2 digit month value", $lookup));
					// TODO check field type
					$condition = sprintf(
						"EXTRACT('month' FROM `%s`) = '%d'",
						$this->getColumnByName($varname),
						$this->prepareValue($value)
					);
					break;

				case "day":
					if (preg_match('/^[0-9]{2}$/', $value)) throw new Exception(sprintf("Lookup '%s' needs an 2 digit day value", $lookup));
					// TODO check field type
					$condition = sprintf(
						"EXTRACT('day' FROM `%s`) = '%d'",
						$this->getColumnByName($varname),
						$this->prepareValue($value)
					);
					break;

				case "isnull":
					$condition = sprintf(
						"`%s` IS %s",
						$this->getColumnByName($varname),
						(($value) ? "NULL" : "NOT NULL")
					);
					break;

				case "search":
					throw new Exception(sprintf("Lookup '%s' is actually not implemented", $lookup));
					break;

				case "regex":
					$condition = sprintf(
						"`%s` REGEXP BINARY '%s'",
						$this->getColumnByName($varname),
						$this->prepareValue($value)
					);
					break;

				case "iregex":
					$condition = sprintf(
						"`%s` REGEXP '%s'",
						$this->getColumnByName($varname),
						$this->prepareValue($value)
					);
					break;

				default:
					throw new Exception(sprintf("Lookup '%s' is actually not implemented", $lookup));
					break;
			}

			$conditions[] = $condition;
		}

		return $conditions;
	}

	/**
	 *
	 * @param 	string 		the field name
	 * @throws 	Exception
	 * @return	string		sql table column of given field
	 */
	private function getColumnByName($varname) {
		$declaration =& $this->_model->getFieldDeclaration($varname);
		if (!isset($this->related) && !$declaration)
			throw new Exception(sprintf("Model %s does not contains a field named '%s'", get_class($this->_model), $varname));

		return (($declaration) ? $declaration->get('db_column') : $varname);
	}

	/**
	 *
	 * @param 	string|array		the value | key-value array
	 * @return	string|array		converted values
	 */
	private function prepareValue($value) {

		if (is_array($value)) {
			foreach ($value as $k => $v) {
				$value[$k] = $this->prepareValue($v);
			}
			return $value;
		}

		if (strtolower($value) == 'null') {
			$value = null;
		}
		elseif (strtolower($value) == 'true') {
			$value = true;
		}
		elseif (strtolower($value) == 'false') {
			$value = false;
		}
		else {
			$dbo =& VFactory::getDatabase();
			$value = $dbo->escape($value);
		}
		return $value;
	}

}