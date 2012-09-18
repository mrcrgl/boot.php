<?php

#VLoader::discover(dirname(__FILE__).DS.'field');

class VModelField extends VObject {

	var $null = false;

	var $blank = false;

	var $type = 'string';

	var $min_length = false;

	var $max_length = false;

	var $field_name = null;

	var $db_column = null;

	var $db_column_type = 'VARCHAR';

	#var $db_column_type = 'VARCHAR';

	var $db_index = false;

	var $default = '';

	var $editable = true;

	var $help_text = null;

	var $primary_key = false;

	var $foreign_key = false;

	var $unique = false;

	var $verbose_name = null;

	var $validators = array();

	var $_model = null;

	static $_instances = array();

	public function __construct($options=array()) {

		foreach ($options as $key => $value) {
			$this->set($key, $value);
		}

	}

	public static function &getInstance($model, $column, $type=null, $options=array()) {
    #print $column.NL;
		if (!isset(self::$_instances[$model]) || !isset(self::$_instances[$model][$column])) {
			if (is_null($type)) {
				// TODO Set Debug message
				#print "Das is scheisse";
				return false;
			}

			$classname = sprintf('VModelField%s', $type);

			if (!class_exists($classname)) {
        VLoader::register($classname, dirname(__FILE__).DS.'field'.DS.(VString::camelcase_to_underscores($type)).'.inc.php');
			  VLoader::autoload($classname);

			  if (!class_exists($classname)) {
			    die( sprintf('Invalid VModelField type received: %s', $type) );
			  }

			}

			self::$_instances[$model][$column] = new $classname($options);
		}

		return self::$_instances[$model][$column];
	}

	public static function prepareModel(&$model) {
    #print "fooo";die("prepareModel called");
		$model_name = get_class(&$model);

		/*if (isset(self::$_instances[$model_name])) {
			return true;
		}*/

		$class_vars = get_class_vars($model_name);
		/*print "<pre>";
		var_dump($class_vars);
		print "</pre>";
		*/
		#$ref =& VModelField::getInstance($model_name, 'uid', 'PrimaryKey', array('db_column' => 'uid'));

		foreach ($class_vars as $column => $declaration) {
			if (preg_match('/^_/', $column)) continue;

			if (!isset(self::$_instances[$model_name][$column])) {
				if (!preg_match('/^(?P<type>\w+):(?P<options>.*)$/', $declaration, $matches)) {
					printf("VModel column declataion layout mismatch: %s<br />", $declaration);
					print "Column: $column".NL;
					var_dump($declaration);print "<br />";
					#throw new Exception(sprintf("VModel column declataion layout mismatch: %s", $declaration));
				}
				#print "<br /><br />";
				#var_dump($matches);print "<br />";


				$options = VArray::parseOptions($matches['options']);

				#var_dump($options);print "<br />";

				#print "<br /><br />";

				$type    = $matches['type'];
				$options = array_merge_recursive($options, array('_model' => &$model, 'db_column' => $column, 'field_name' => $column));


				#print 'prepareModel said: '.$column.NL;
				$ref =& VModelField::getInstance($model_name, $column, $type, $options);
			}
			else {
				$ref =& VModelField::getInstance($model_name, $column);
			}
			$model->set($column, $ref->default, true);
		}
	}

	public function onInitialize($value) {
		#printf("onInitialize(%s) called".NL, $value);
		return $value;
	}

	public function onCreate($value) {
		#printf("onCreate(%s) called".NL, $value);
		return $value;
	}

	public function onUpdate($value) {
		#printf("onUpdate(%s) called".NL, $value);
		return $value;
	}

	public function onSet($value) {
		#printf("onSet(%s) called".NL, $value);
		return $value;
	}

	public function onGet($value) {
		#printf("onGet(%s) called".NL, $value);
		return $value;
	}
}