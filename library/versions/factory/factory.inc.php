<?php

abstract class VFactory {

	public static $template = null;

	public static $language = null;

	public static $database = null;

	public static $router = null;

	public static $input = null;

	public static $controller = null;

	public static $document = null;

	public static $url = null;

	public static $user = null;

	/**
	 * @var    VSession
	 * @since  2.0
	 */
	public static $session = null;

	static public function getTemplate($instance='default') {

		if (!self::$template) {
			self::$template = array();
		}

		if (!array_key_exists($instance, self::$template) || !is_object(self::$template[$instance])) {

			if (!class_exists('VTemplate')) {
				VLoader::import('versions.output.template');
			}

			self::$template[$instance] = self::createTemplate(null, $instance);
		}

		return self::$template;
	}

	static public function getLanguage($instance='default') {

		if (!self::$language) {
			self::$language = array();
		}

		if (!array_key_exists($instance, self::$language) || !is_object(self::$language[$instance])) {

			self::$language[$instance] = self::createLanguage(null, $instance);
		}

		return self::$language[$instance];
	}

	static public function getDatabase($instance='default') {

		if (!self::$database) {
			self::$database = array();
		}

		if (!array_key_exists($instance, self::$database) || !is_object(self::$database[$instance])) {

			self::$database[$instance] = self::createDatabase(null, $instance);
		}

		return self::$database[$instance];
	}

	static public function getRouter($instance='default') {

		if (!self::$router) {
			self::$router = array();
		}

		if (!array_key_exists($instance, self::$router) || !is_object(self::$router[$instance])) {

			self::$router[$instance] = new VApplicationRouter($instance);
		}

		return self::$router[$instance];
	}

	static public function getInput($instance='default') {

		if (!self::$input) {
			self::$input = array();
		}

		if (!array_key_exists($instance, self::$input) || !is_object(self::$input[$instance])) {

			self::$input[$instance] = VInput::getInstance();
		}

		return self::$input[$instance];
	}

	static public function getController($instance='default') {

		if (!self::$controller) {
			self::$controller = array();
		}

		if (!array_key_exists($instance, self::$controller) || !is_object(self::$controller[$instance])) {

			self::$controller[$instance] = VApplicationController::getInstance();
		}

		return self::$controller[$instance];
	}

	static public function getDocument($instance='default') {

		if (!self::$document) {
			self::$document = array();
		}

		if (!array_key_exists($instance, self::$document) || !is_object(self::$document[$instance])) {

			self::$document[$instance] = VDocument::getInstance();
		}

		return self::$document[$instance];
	}

	/**
	 * Get a session object.
	 *
	 * Returns the global {@link VSession} object, only creating it if it doesn't already exist.
	 *
	 * @return  VSession object
	 *
	 * @see     VSession
	 * @since   2.0
	 */
	public static function getSession() {
		if (!self::$session) {
			self::$session = self::createSession();
		}

		return self::$session;
	}

	public static function getUrl() {
		if (!self::$url) {
			self::$url = VUrl::getInstance();
		}

		return self::$url;
	}

	public static function getUser() {
	  if (!self::$user) {

	    $session =& self::getSession();
	    $login =& $session->get('login');
	    if ($login && is_object($login) && $login->loggedIn()) {
	      self::$user = new User();
	      self::$user->load($login->obj->uid);
	    } else {
	      return true;
	    }
	  }

	  return self::$user;
	}

	protected static function createTemplate($type=null, $instance='default') {

		if (!$type) {
			$type = VSettings::f('application.template', 'smarty');
		}

		return VTemplate::getInstance($type);
	}

	protected static function createLanguage($langcc=null, $instance='default') {

		if (!$langcc) {
			$langcc = VSettings::f('default.language', 'de');
		}

		$manager = new LanguageManager();

		return $manager->getByCountryCode($langcc);
	}

	protected static function createDatabase($adapter=null, $instance='default') {

		if (!$adapter) {
			$adapter = VSettings::f('database.adapter', 'mysql');
		}

		return VDatabase::getInstance($adapter);
	}

	/**
	 * Create a session object
	 *
	 * @return  VSession object
	 *
	 * @since   2.0
	 */
	protected static function createSession($options = array()) {
		// Get the editor configuration setting
		$handler = VSettings::f('session.handler', 'none');

		// Config time is in minutes
		$options['expire'] = (VSettings::f('session.lifetime')) ? VSettings::f('session.lifetime') * 60 : 900;

		$session = VSession::getInstance($handler, $options);
		#var_dump($session);
		if ($session->getState() == 'expired') {
			$session->restart();
		}

		return $session;
	}

}