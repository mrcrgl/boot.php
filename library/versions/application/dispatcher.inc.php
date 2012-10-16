<?php



VLoader::register('Validator', VLIB.DS.'versions'.DS.'utilities'.DS.'validator.inc.php');
VLoader::discover(dirname(__FILE__).DS.'dispatcher');

class VDispatcher {

	/**
	 * @var $controller Controller
	 */
	var $dispatcher = null;

	public function __construct() {

		if (is_null($this->dispatcher)) {
			$this->dispatcher = $this->getInstance( VSettings::f('application.dispatcher', 'default') );
		}

	}

	private function getInstance($type='default') {

		$classname = 'VDispatcher'.ucfirst($type);

		if (!class_exists($classname)) {
			throw new Exception( sprintf('Dispatcher %s not found. Exiting...', $classname) );
			//user_error()
		}

		return new $classname();
	}

	public function __call($method, $args) {
		return call_user_func_array(array($this->dispatcher, $method), $args);
	}

	static public function __callStatic($method, $args) {
		throw new Exception('Dispatcher: __callStatic not implemented yet.');
	}
}