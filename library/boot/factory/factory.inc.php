<?php

abstract class BFactory
{

    public static $template = null;

    public static $language = null;

    public static $database = null;

    public static $router = null;

    public static $oInput = null;

    public static $controller = null;

    public static $document = null;

    public static $url = null;

    public static $user = null;

    /**
     * @var    BSession
     * @since  2.0
     */
    public static $session = null;

    static public function getTemplate($instance='default')
   {

        if (!self::$template) {
            self::$template = array();
        }

        if (!array_key_exists($instance, self::$template) || !is_object(self::$template[$instance])) {

            if (!class_exists('BTemplate'))
{
                BLoader::import('boot.output.template');
            }

            self::$template[$instance] = self::createTemplate(null, $instance);
        }

        return self::$template;
    }

    static public function getLanguage($instance='default')
    {

        if (!self::$language) {
            self::$language = array();
        }

        if (!array_key_exists($instance, self::$language) || !is_object(self::$language[$instance])) {

            self::$language[$instance] = self::createLanguage(null, $instance);
        }

        return self::$language[$instance];
    }

    static public function getDatabase($instance='default')
    {

        if (!self::$database) {
            self::$database = array();
        }

        if (!array_key_exists($instance, self::$database) || !is_object(self::$database[$instance])) {

            self::$database[$instance] = self::createDatabase(null, $instance);
        }

        return self::$database[$instance];
    }

    static public function getRouter($instance='default')
    {

        if (!self::$router) {
            self::$router = array();
        }

        if (!array_key_exists($instance, self::$router) || !is_object(self::$router[$instance])) {

            self::$router[$instance] = new BApplicationRouter($instance);
        }

        return self::$router[$instance];
    }

    static public function getInput($instance='default')
    {

        if (!self::$oInput) {
            self::$oInput = array();
        }

        if (!array_key_exists($instance, self::$oInput) || !is_object(self::$oInput[$instance])) {

            self::$oInput[$instance] = BInput::getInstance();
        }

        return self::$oInput[$instance];
    }

    static public function getController($instance='default')
    {

        if (!self::$controller) {
            self::$controller = array();
        }

        if (!array_key_exists($instance, self::$controller) || !is_object(self::$controller[$instance])) {

            self::$controller[$instance] = BApplicationController::getInstance();
        }

        return self::$controller[$instance];
    }

    static public function getDocument($instance='default')
    {

        if (!self::$document) {
            self::$document = array();
        }

        if (!array_key_exists($instance, self::$document) || !is_object(self::$document[$instance])) {

            self::$document[$instance] = BDocument::getInstance();
        }

        return self::$document[$instance];
    }

    /**
     * Get a session object.
     *
     * Returns the global {@link BSession} object, only creating it if it doesn't already exist.
     *
     * @return  BSession object
     *
     * @see     BSession
     * @since   2.0
     */
    public static function getSession()
     {
        if (!self::$session) {
            self::$session = self::createSession();
        }

        return self::$session;
    }

    public static function getUrl()
    {
        if (!self::$url) {
            self::$url = BUrl::getInstance();
        }

        return self::$url;
    }

    public static function getUser()
    {
      if (!self::$user) {

        $oSession =& self::getSession();
        $oLogin =& $oSession->get('login');
        if ($oLogin && is_object($oLogin) && $oLogin->loggedIn()) {
          self::$user = new User();
          self::$user->load($oLogin->obj->uid);
        } else {
          return true;
        }
      }

      return self::$user;
    }

    protected static function createTemplate($type=null, $instance='default')
    {

        if (!$type) {
            $type = BSettings::f('application.template', 'smarty');
        }

        return BTemplate::getInstance($type);
    }

    protected static function createLanguage($langcc=null, $instance='default')
    {

        if (!$langcc) {
            $langcc = BSettings::f('default.language', 'de');
        }

        $manager = new LanguageManager();

        return $manager->getByCountryCode($langcc);
    }

    protected static function createDatabase($adapter=null, $instance='default')
    {

        if (!$adapter) {
            $adapter = BSettings::f('database.adapter', 'mysql');
        }

        return BDatabase::getInstance($adapter);
    }

    /**
     * Create a session object
     *
     * @return  BSession object
     *
     * @since   2.0
     */
    protected static function createSession($options = array())
     {
        // Get the editor configuration setting
        $handler = BSettings::f('session.handler', 'none');

        // Config time is in minutes
        $options['expire'] = (BSettings::f('session.lifetime')) ? BSettings::f('session.lifetime') * 60 : 900;

        $oSession = BSession::getInstance($handler, $options);
        #var_dump($session);
        if ($oSession->getState() == 'expired') {
            $oSession->restart();
        }

        return $oSession;
    }

}