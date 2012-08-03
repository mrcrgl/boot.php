<?php
/**
 * The global controller. All other controllers should extend of this one.
 *
 * @copyright 2008 / riegel.it
 * @author Marc Riegel <mr@riegel.it>
 * @version $Revision: 1.8 $
 */
class RequestDispatcher {

	/**
	 * The default View
	 *
	 * @var _viewDefault
	 */
	protected $_viewDefault = 'index';

	/**
	 * The current View object
	 *
	 * @var _view
	 */
	protected $_view;

	/**
	 * The cached View object
	 *
	 * @var _viewCached
	 */
	protected $_viewCached;

	/**
	 * The current process Step
	 *
	 * @var   _step
	 * @value prepare, proceed, show, finish
	 */
	protected $_step = 'prepare';

	/**
	 * Arguments of the request string
	 *
	 * @var   _args
	 */
	public $_args = array();

	/**
	 * Requested Page without Params
	 *
	 * @var   _requestPage
	 */
	public $_requestPage = '';

	/**
	 * The current Ajax Node
	 *
	 * @var _ajaxNode
	 */
	public $_ajaxNode;

	/**
	 * The $_SERVER array
	 *
	 * @var _SERVER
	 */
	public $_SERVER = array();

	/**
	 * The $_POST array
	 *
	 * @var _POST
	 */
	public $_POST = array();

	/**
	 * The $_FILES array
	 *
	 * @var _FILES
	 */
	public $_FILES = array();

	/**
	 * Is the Browser a SearchEngine?
	 *
	 * @var isSearchEngine
	 * @value bool
	 */
	public $isSearchEngine;


	public function __construct() {

	}
	
	public function getViewName() {
		if (is_object($this->_view)) {
			return (string)$this->_view;
		}
		return false;
	}
	
	public function processRequest($requestArgs = false) {
		/**
		 * if $requestArgs eq false, this is no Ajax Request
		 */
		$this->prepareProcessRequest((is_array($requestArgs)) ? $requestArgs : false);
		$activeView = $this->getRequestView();
		$this->prepareNavigation($activeView);
		$this->changeView($activeView);
		$this->subrequest();
	}

	protected function prepareProcessRequest($requestArgs) {
		//print_r($requestArgs);
		$this->prepareEnv();
		if (!is_array($requestArgs)) {
			$this->checkBrowser();
			$this->prepareParam();
			$this->checkRedirect();
			$this->prepareArgs();
		} else {
			$this->prepareAjaxParam($requestArgs);
			$this->prepareAjaxArgs($requestArgs);
		}
	}

	protected function prepareEnv() {

		/*
		 * TODO: prepare watch
		 */
		#ENV::$startTime = microtime();

		/*
		 * prepare Smarty
		 */
		#VInstance::_new(new Smarty(), 'template');
		/**/

		return;
	}

	protected final function prepareParam() {
		if (is_array($_POST)) {
			foreach ($_POST as $key => $value) {
				$this->_POST[$key] = $value;
			}
		}
		if (is_array($_SERVER)) {
			foreach ($_SERVER as $key => $value) {
				$this->_SERVER[$key] = $value;
			}
		}
		if (is_array($_FILES)) {
			foreach ($_FILES as $key => $value) {
				$this->_FILES[$key] = $value;
			}
		}
		return;
	}

	protected final function prepareAjaxParam($requestArgs) {
		$iPOST = $requestArgs[2];
		if (is_array($iPOST)) {
			foreach ($iPOST as $key => $value) {
				$this->_POST[$key] = $value;
			}
		}
		if (is_array($_SERVER)) {
			foreach ($_SERVER as $key => $value) {
				$this->_SERVER[$key] = $value;
			}
		}

		return;
	}

	protected final function checkBrowser() {
		
		$browser_info = $this->getUserAgent();
		
		if ($browser_info['browser'] == 'MSIE' && ($browser_info['version'] < 8) ) {
			VFactory::getTemplate()->display('incompatible_user_agent.htpl');
			exit;
		}
		
	}

	protected final function checkRedirect() {
		if (strstr($_SERVER['REQUEST_URI'], "#")) {
			die("Wir haben ihn!");
			list($trash, $link) = split("#", $this->_SERVER['REQUEST_URI']);
			Header("Location: $link");
		}
		#print "Wir haben ihn!";
	}

	protected final function prepareArgs() {
		$requestArgs = $this->_SERVER['REQUEST_URI'];



		$this->_args = explode("/",$requestArgs);
		$this->_requestPage = substr($requestArgs, 0, strpos($requestArgs, 'html')+4);

		return null;
	}

	protected final function prepareAjaxArgs($requestArgs) {
		$this->_ajaxNode = $requestArgs[0];
		$this->_args     = explode("/",$requestArgs[1]);

		return null;
	}

	function prepareNavigation($_requestedUrl) {
		#
	}

	function getRequestView() {
		$newView   = (isset($this->_args[1]) && strlen($this->_args[1]) > 0) ? false : $this->_viewDefault;
		if ($newView !== false) {
			list($newView, $trash) = explode('.', $newView);
			return $newView;
		}

		$newView     = '';
		$newArgs     = array();
		$viewFound   = false;
		$argKey      = false;
		$n           = 0;


		foreach ($this->_args as $part) {
			if ($viewFound == false) {
				if (substr($part, -4) == "html") {
					$viewFound = true;
					list($part, $trash) = explode('.', $part);
				}
				$newView     .= $part;
			} else {
				$newArgs[$n] = $part;
				$n++;

				if ($argKey) {
					$newArgs[$argKey] = $part;
					$argKey = false;
				} else {
					$argKey = $part;
				}
			}
		}


		/*
		 * Overwrite old Arguments
		 */
		$this->_args = $newArgs;

		#ENV::$parse['_view'] = $newView;


		return $newView;
	}

	function headerLocation($num, $url, $exit=false){
		static $http = array (
		100 => "HTTP/1.1 100 Continue",
		101 => "HTTP/1.1 101 Switching Protocols",
		200 => "HTTP/1.1 200 OK",
		201 => "HTTP/1.1 201 Created",
		202 => "HTTP/1.1 202 Accepted",
		203 => "HTTP/1.1 203 Non-Authoritative Information",
		204 => "HTTP/1.1 204 No Content",
		205 => "HTTP/1.1 205 Reset Content",
		206 => "HTTP/1.1 206 Partial Content",
		300 => "HTTP/1.1 300 Multiple Choices",
		301 => "HTTP/1.1 301 Moved Permanently",
		302 => "HTTP/1.1 302 Found",
		303 => "HTTP/1.1 303 See Other",
		304 => "HTTP/1.1 304 Not Modified",
		305 => "HTTP/1.1 305 Use Proxy",
		307 => "HTTP/1.1 307 Temporary Redirect",
		400 => "HTTP/1.1 400 Bad Request",
		401 => "HTTP/1.1 401 Unauthorized",
		402 => "HTTP/1.1 402 Payment Required",
		403 => "HTTP/1.1 403 Forbidden",
		404 => "HTTP/1.1 404 Not Found",
		405 => "HTTP/1.1 405 Method Not Allowed",
		406 => "HTTP/1.1 406 Not Acceptable",
		407 => "HTTP/1.1 407 Proxy Authentication Required",
		408 => "HTTP/1.1 408 Request Time-out",
		409 => "HTTP/1.1 409 Conflict",
		410 => "HTTP/1.1 410 Gone",
		411 => "HTTP/1.1 411 Length Required",
		412 => "HTTP/1.1 412 Precondition Failed",
		413 => "HTTP/1.1 413 Request Entity Too Large",
		414 => "HTTP/1.1 414 Request-URI Too Large",
		415 => "HTTP/1.1 415 Unsupported Media Type",
		416 => "HTTP/1.1 416 Requested range not satisfiable",
		417 => "HTTP/1.1 417 Expectation Failed",
		500 => "HTTP/1.1 500 Internal Server Error",
		501 => "HTTP/1.1 501 Not Implemented",
		502 => "HTTP/1.1 502 Bad Gateway",
		503 => "HTTP/1.1 503 Service Unavailable",
		504 => "HTTP/1.1 504 Gateway Time-out"
		);
		header($http[$num]);
		header ("Location: $url");
		if ($exit) {
			session_write_close();
			exit;
		}
	}

	protected final function subrequest() {

		while($this->_step != 'finish') {
			switch($this->_step) {
				case 'prepare':
					$this->prepareView();
					break;

				case 'proceed':
					$this->parseTail();
					$this->proceedView();
					break;

				case 'show':
					$this->showView();
					break;
			}
		}

		return;
	}

	public function changeView($_view, $bCache=FALSE) {
		if (isset($this->_view)) {
			if ($bCache == TRUE) {
				$this->_viewCached = &$this->_view;
			}

			unset($this->_view);
		}

		if (!isset($this->arrView[$_view]) && !isset($this->arrView[strtolower($_view)])) {
			throw new Exception("VIEW \"$_view\" not found in arrView[]!");
		}
		$_view = (!isset($this->arrView[$_view]) && isset($this->arrView[strtolower($_view)])) ? strtolower($_view) : $_view;

		if (!$this->checkLoginRequired($_view)) {
			return false;
		}

		if (!$this->checkPermission($_view)) {
			return false;
		}

		$this->_view = new $this->arrView[$_view][0]();
		$this->_view->setDispatcher($this);

		return $this->_view;
	}

	protected function prepareView() {
		$this->_step = $this->_view->prepare();
	}

	protected function proceedView() {
		$this->_step = $this->_view->proceed();
	}

	protected function showView() {
		$this->_step = $this->_view->show();
		$this->_step = 'finish';
	}

	protected function parseTail() {
		$this->smartyAutoParse();
		$this->setDebugging();
	}

	protected function checkLoginRequired($_view) {
		#VFactory::getTemplate()->assign('default_landing_page');
		if ((is_array($this->arrView[$_view][1]) || $this->arrView[$_view][1] == true) && !VInstance::f('Login')->loggedIn()) {
			if (!in_array(strtolower($this->_SERVER['REQUEST_URI']), array('/', '/index.html'))) {
				VFactory::getTemplate()->assign('requested_view', $this->_SERVER['REQUEST_URI']);
				VFactory::getTemplate()->assign('error', "loginRequired");
			} else {
				VFactory::getTemplate()->assign('requested_view', VInstance::f('LinkGenerator')->getLink($this->strLandingPage));
			}
			$this->changeView('login');
			return false;
		}
		return true;
	}

	protected function checkPermission($_view) {
		$hasPermission = false;

		if (!is_array($this->arrView[$_view][1])) {
			return true;
		}

		foreach ($this->arrView[$_view][1] as $right) {
			if ($hasPermission === true) {
				continue;
			}

			if (VInstance::f('Login')->obj->hasPermission($right)) {
				$hasPermission = true;
			}
		}

		if ($hasPermission === false) {
			VInstance::f('Login')->obj->log($this, "Permission denied by changing View to ".$this->arrView[$_view][0]);

			VFactory::getTemplate()->assign('requested_view', $this->_SERVER['REQUEST_URI']);
			VFactory::getTemplate()->assign('system_error', "permissionDenied");
			$this->changeView('Error');
			return false;
		}

		return $hasPermission;
	}

	protected function setDebugging() {
		if ( VSettings::f('default.debug', false) ) {
			/* TODO
			 * 
			 * VFactory::getTemplate()->assign('debug', ENV::$debug);
			VFactory::getTemplate()->assign('count_read_querys', ENV::$countSQLQuerys['r']);
			VFactory::getTemplate()->assign('count_write_querys', ENV::$countSQLQuerys['w']);
			VFactory::getTemplate()->assign('count_querys', ENV::$countSQLQuerys['r'] + ENV::$countSQLQuerys['w']);
			VFactory::getTemplate()->assign('SQLQueryLog', ENV::$SQLQueryLog);
			VFactory::getTemplate()->assign('generate_time', (microtime() - ENV::$startTime));*/
		}
	}

	protected function smartyAutoParse() {
		/* TODO
		 * 
		 * if (is_array(ENV::$parse)) {
			foreach (ENV::$parse as $key => $value) {
				VFactory::getTemplate()->assign($key, $value);
			}
		}

		if (is_array(ENV::$parseFnc)) {
			foreach (ENV::$parseFnc as $key => $value) {
				if (function_exists($value)) {
					VFactory::getTemplate()->register_function($key, $value);
				}
			}
		}
		if (is_array(ENV::$parseSrc)) {
			foreach (ENV::$parseSrc as $key => $value) {
				VFactory::getTemplate()->register_resource($key, $value);
			}
		}
		if (is_array(ENV::$parseModifier)) {
			foreach (ENV::$parseModifier as $key => $value) {
				VFactory::getTemplate()->register_modifier($key, $value);
			}
		}*/
	}

	protected final function startSession() {
		//
	}

	protected final function closeSession() {
		//
	}

	protected final function destroySession() {
		//
	}

	function getUserAgent( $u_agent = false ) {

		if ($u_agent === false) $u_agent = $_SERVER['HTTP_USER_AGENT'];
		$data = array();

		# ^.+?(?<platform>Android|iPhone|iPad|Windows|Macintosh|Windows Phone OS)(?: NT)*(?: [0-9.]+)*(;|\))
		if (preg_match('/^.+?(?P<platform>Android|iPhone|iPad|Windows|Macintosh|Windows Phone OS)(?: NT)*(?: [0-9.]+)*(;|\))/im', $u_agent, $regs)) {
			$data['platform'] = $regs['platform'];
		} else {
			$result = "";
		}

		# (?<browser>Camino|Kindle|Firefox|Safari|MSIE|AppleWebKit|Chrome|IEMobile|Opera)(?:[/ ])(?<version>[0-9.]+)
		preg_match_all('%(?P<browser>Camino|Kindle|Firefox|Safari|MSIE|AppleWebKit|Chrome|IEMobile|Opera)(?:[/ ])(?P<version>[0-9.]+)%im', $u_agent, $result, PREG_PATTERN_ORDER);

		if( $result['browser'][0] == 'AppleWebKit' ) {
			if( ( $data['platform'] == 'Android' && !($key = 0) ) || $key = array_search( 'Chrome', $result['browser'] ) ) {
				$data['browser'] = 'Chrome';
			}elseif( $key = array_search( 'Kindle', $result['browser'] ) ) {
				$data['browser'] = 'Kindle';
			}elseif( $key = array_search( 'Safari', $result['browser'] ) ) {
				$data['browser'] = 'Safari';
			}else{
				$key = 0;
				$data['browser'] = 'webkit';
			}
			$data['version'] = $result['version'][$key];
		}elseif( $key = array_search( 'Opera', $result['browser'] ) ) {
			$data['browser'] = $result['browser'][$key];
			$data['version'] = $result['version'][$key];
		}elseif( $result['browser'][0] == 'MSIE' ){
			if( $key = array_search( 'IEMobile', $result['browser'] ) ) {
				$data['browser'] = 'IEMobile';
			}else{
				$data['browser'] = 'MSIE';
				$key = 0;
			}
			$data['version'] = $result['version'][$key];
		}else{
			$data['browser'] = $result['browser'][0];
			$data['version'] = $result['version'][0];
		}

		if( $data['browser'] == 'Kindle' ) {
			$data['platform'] = 'Kindle';
		}

		return $data;

	}

}

?>