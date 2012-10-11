<?php

/**
 * Class to handle user input
 *
 * @package     Versions.core
 * @subpackage  Application
 * @since       2.0
 */
class VInput {

	/**
	 * Options array for the VInput instance.
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $options = array();

	/**
	 * Filter object to use.
	 *
	 * @var    VFilterInput
	 * @since  2.0
	 */
	protected $filter = null;

	/**
	 * Input data.
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $data = array();

	/**
	 * Input objects
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $inputs = array();

	/**
	 * Constructor.
	 *
	 * @param   array  $source   Source data (Optional, default is $_REQUEST)
	 * @param   array  $options  Array of configuration parameters (Optional)
	 *
	 * @since   2.0
	 */
	public function __construct($source = null, array $options = array()) {

		if (is_null($source))	{
			if (strtolower(PHP_SAPI) == 'cli') {
				$cli = new VInputCli($options);
				// TODO integrate cli input data
			} else {
				$this->data = & $_REQUEST;
			}


		} else {
			$this->data = & $source;
		}

		// Set the options for the class.
		$this->options = $options;
	}

	static public function getInstance($type=null) {

		if (!$type) {
			$type = self::getInputType();
		}

		$classname = 'VInput'.ucfirst($type);

		VLoader::autoload($classname);

		if (!class_exists($classname)) {
			throw new Exception( sprintf('Input engine %s not found. Exiting...', $classname) );
		}

		return new $classname();
	}

	protected static function getInputType() {
		if (strtolower(PHP_SAPI) == 'cli') {
			return 'cli';
		} else {
			return 'web';
		}
	}

	/**
	 * Magic method to get an input object
	 *
	 * @param   mixed  $name  Name of the input object to retrieve.
	 *
	 * @return  VInput  The request input object
	 *
	 * @since   2.0
	 */
	public function __get($name) {
		if (isset($this->inputs[$name])) {
			return $this->inputs[$name];
		}

		$className = 'VInput' . $name;
		if (class_exists($className))	{
			$this->inputs[$name] = new $className(null, $this->options);
			return $this->inputs[$name];
		}

		// TODO throw an exception
	}

	/**
	 * Gets a value from the input data.
	 *
	 * @param   string  $name     Name of the value to get.
	 * @param   mixed   $default  Default value to return if variable does not exist.
	 * @param   string  $filter   Filter to apply to the value.
	 *
	 * @return  mixed  The filtered input value.
	 *
	 * @since   2.0
	 */
	public function get($name, $default = null, $filter = 'cmd') {
		if (isset($this->data[$name])) {
			return $this->filter->clean($this->data[$name], $filter);
		}

		return $default;
	}

	/**
	 * Gets an array of values from the request.
	 *
	 * @param   array  $vars        Associative array of keys and filter types to apply.
	 * @param   mixed  $datasource  Array to retrieve data from, or null
	 *
	 * @return  mixed  The filtered input data.
	 *
	 * @since   2.0
	 */
	public function getArray(array $vars, $datasource = null) {
		$results = array();

		foreach ($vars as $k => $v)	{
			if (Validator::is($v, 'array')) {
				if (is_null($datasource))	{
					$results[$k] = $this->getArray($v, $this->get($k, null, 'array'));
				}	else {
					$results[$k] = $this->getArray($v, $datasource[$k]);
				}
			}	else {
				if (is_null($datasource))	{
					$results[$k] = $this->get($k, null, $v);
				}	else {
					$results[$k] = $this->filter->clean($datasource[$k], $v);
				}
			}
		}
		return $results;
	}

	/**
	 * Sets a value
	 *
	 * @param   string  $name   Name of the value to set.
	 * @param   mixed   $value  Value to assign to the input.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function set($name, $value) {
		$this->data[$name] = $value;
	}

	/**
	 * Magic method to get filtered input data.
	 *
	 * @param   mixed   $name       Name of the value to get.
	 * @param   string  $arguments  Default value to return if variable does not exist.
	 *
	 * @return  boolean  The filtered boolean input value.
	 *
	 * @since   2.0
	 */
	public function __call($name, $arguments)
	{
		if (substr($name, 0, 3) == 'get')	{

			$filter = substr($name, 3);

			$default = null;
			if (isset($arguments[1]))	{
				$default = $arguments[1];
			}

			return $this->get($arguments[0], $default, $filter);
		}
	}

	/**
	 * Gets the request method.
	 *
	 * @return  string   The request method.
	 *
	 * @since		2.0
	 */
	public function getMethod() {
		$method = strtoupper($_SERVER['REQUEST_METHOD']);
		return $method;
	}

}