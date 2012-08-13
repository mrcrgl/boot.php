<?php 

VLoader::discover(dirname(__FILE__).DS.'field');

class VModelField extends VObject {
	
	var $null = false;
	
	var $blank = false;
	
	var $type = 'string';

	var $min_length = false;
	
	var $max_length = false;
	
	var $db_column = null;
	
	var $db_column_type = 'VARCHAR';
	
	#var $db_column_type = 'VARCHAR';
	
	var $db_index = false;
	
	var $default = '';
	
	var $editable = true;
	
	var $help_text = null;
	
	var $primary_key = false;
	
	var $unique = false;
	
	var $verbose_name = null;
	
	var $validators = array();
	
	static $_instances = array();
	
	public function __construct($options=array()) {
		
		foreach ($options as $key => $value) {
			$this->set($key, $value);			
		}
		
	}
	
	public static function getInstance($model, $column, $type=null, $options=array()) {
		
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
	}
	
	public static function prepareModel($model) {
		
		$model_name = get_class($model);
		
		if (isset(self::$_instances[$model_name])) {
			return true;
		}
		
		$class_vars = get_class_vars($model_name);
		/*print "<pre>";
		var_dump($class_vars);
		print "</pre>";
		*/
		#$ref =& VModelField::getInstance($model_name, 'uid', 'PrimaryKey', array('db_column' => 'uid'));
		
		foreach ($class_vars as $column => $declaration) {
			if (preg_match('/^_/', $column)) continue;
			if (!preg_match('/^(?P<type>\w+):\[(?P<options>.*)\]$/', $declaration, $matches)) {
				printf("VModel column declataion layout mismatch: %s<br />", $declaration);
				var_dump($declaration);print "<br />";
				#throw new Exception(sprintf("VModel column declataion layout mismatch: %s", $declaration));
			}
			#var_dump($matches);print "<br />";
			
			$type    = $matches['type'];
			$options = array(
				'db_column' => $column
			);
			
			if (strlen($matches['options'])) {
				$option_pairs = explode(',', $matches['options']);
				foreach ($option_pairs as $option_pair) {
					$option_pair = trim($option_pair);
					#print $option_pair.NL;
					
					list($key, $value) = explode(':', $option_pair);
					$key = trim($key);
					$value = trim($value);
					
					if (strtolower($value) == 'true') {
						$options[$key] = true;
					}
					elseif (strtolower($value) == 'false') {
						$options[$key] = false;
					}
					elseif (strtolower($value) == 'null') {
						$options[$key] = null;
					}
					else {
						$options[$key] = $value;
					}
				}
			}
			
			// call the vmodel::method
			#var_dump($options);print "<br />";
			
			$ref =& VModelField::getInstance($model_name, $column, $type, $options);
			$model->set($column, $ref->default, true);
		}
	}
}