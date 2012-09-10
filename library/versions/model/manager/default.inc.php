<?php
/**
 * @build   11.09.2008
 * @project DMTp
 * @package DefaultManager
 *
 * @author  Marc Riegel
 * @contact riegel@it-t.de
 *
 * @deprecated will be removed in future
 * --
 *
 * --
 */
abstract class VModelManagerDefault extends VObject {

  var $limit = "none";

  var $display_deleted = false;

  var $display_subuser = false;

  var $customer_filter = false;

  var $website_filter = false;

  var $hotel_filter = false;

  var $check_owner = false;

  var $ignore_object_state = false;

  var $object_name = null;

  var $object = null;

  var $_filter = array();

  var $_order = array();

	/**
   *
   * Default constructor
   */
  function __construct() {

  	$object = str_replace("Manager", "", get_class($this));
  	$this->object_name = $object;
  	$this->object = new $object();

  }

  /**
   *
   * Add filter
   * @param string $column
   * @param string $value
   * @param string $condition
   */
  public function filter($column, $value=null, $condition='eq') {
  	if (!$this->object->hasField($column)) {
  		return false;
  	}

  	switch ($condition) {
  		case "eq":
  			$cond = '=';
  			$value = "'".$value."'";
  			break;
  		case "neq":
  			$cond = '!=';
  			$value = "'".$value."'";
  			break;
  		case "lt":
  			$cond = '>';
  			$value = "'".$value."'";
  			break;
  		case "gt":
  			$cond = '<';
  			$value = "'".$value."'";
  			break;
  		case "like":
  			$cond = '<';
  			$value = "'".$value."'";
  			break;
  		case "in":
  			$cond = 'IN';
  			if (!is_array($value)) return false;
  			$value = "('".implode("', '", $value)."')";
  			break;
  		case "not-in":
  			$cond = 'IN';
  			if (!is_array($value)) return false;
  			$value = "('".implode("', '", $value)."')";
  			break;
  		case "null":
  			$cond = 'IS NULL';
  			$value = '';
  			break;
  		case "not-null":
  			$cond = 'IS NOT NULL';
  			$value = '';
  			break;
  	}

  	$clause = sprintf(" (`%s` %s %s) ", $column, $cond, $value);
  	$this->_filter[] = $clause;

  	return true;
  }

  public function order_by($field, $dir='ASC') {
    if (!in_array(strtoupper($dir), array('ASC', 'DESC'))) die("Argument 2 'dir' must be ASC or DESC");

    if (!$this->object->hasField($field)) {
      return false;
    }

    $this->_order[] = sprintf("`%s` %s", $field, $dir);
  }

	public function clearFilter() {
  	$this->$_filter = array();
  }

  /**
   *
   * Limit the result
   * @param int $count
   * @param int $offset
   */
  public function limit($count, $offset=0) {
  	$this->limit = sprintf("%d, %d", $offset, $count);
  }

  /*
   * getObjects
   *
   * arrResult - Array with the containing uid to initialize the object
   * strObject - Name of the Object
   * unique    - Name of the unique key
   *
   * @return array of objects
   */
  protected function getObjects($arrResult, $strObject, $unique='uid') {
    $arrObjects = array();

    foreach ($arrResult as $key => $row) {
      if ( !isset($row[$unique]) ) {
        throw new Exception("getObjects failed! Unique '$unique' not found in Result-List.");
      }
      $arrObjects[$key] = new $strObject($row[$unique]);
    }
    return $arrObjects;
  }

  function getUserListString() {
    /*if ($this->display_subuser) {
      return Instance::f("Login")->obj->customer->all_sub_customer_string;
    } else {
      return "'".Instance::f("Login")->obj->customer_uid."'";
    }*/
  }

  function getOrdering() {

  	$order = array();
    if (count($this->_order) <= 0 && $this->object->hasField('priority')) {
      $order[] = sprintf(" `priority` ASC");
    }

    foreach ($this->_order as $o) {
      $order[] = $o;
    }

    if (count($order) > 0) {
      return implode(', ', $order);
    }

  	return "none";
  }

  function getLimitCondition() {
  	return (isset($this->pagination)) ? $this->pagination->getLimitStatement() : $this->limit;
  }

  function getWhereCondition($where='') {
    if (!empty($where)) {
      $where = "($where)";
    } else {
      $where = "1";
    }

    if (!$this->display_deleted && !$this->ignore_object_state && $this->object->hasField('status')) {
      $where .= sprintf(" AND `status` >= %d", VSettings::f('default.min_object_state', 1));
    }

    if ($this->check_owner && $this->object->hasField('customer_uid')) {
      $where .= " AND `customer_uid` IN (".$this->getUserListString().") ";
    }

    if ($this->customer_filter && $this->object->hasField('customer_uid')) {
      $where .= " AND `customer_uid` = '".$this->customer_filter."' ";
    }

  	if ($this->hotel_filter && $this->object->hasField('hotel_uid')) {
      $where .= " AND `hotel_uid` = '".$this->hotel_filter."' ";
    }

    if (count($this->_filter)) {
    	$where .= " AND ".implode(" AND ", $this->_filter);
    }

    return $where;
  }
}
?>