<?php
/**
 * @package     boot.php.core
 * @subpackage  Session
 *
 * @copyright
 * @license
 */

// Register the session storage class with the loader
#BLoader::register('BSessionStorage', dirname(__FILE__) . '/storage.inc.php');

BLoader::import('boot.environment.request');
BLoader::import('boot.session.storage');

/**
 * Class for managing HTTP sessions
 *
 * Provides access to session-state values as well as session-level
 * settings and lifetime management methods.
 * Based on the standard PHP session handling mechanism it provides
 * more advanced features such as expire timeouts.
 *
 * @package     boot.php.core
 * @subpackage  Session
 * @since       2.0
 */
class BSession extends BObject
{

    /**
     * Internal state.
     * One of 'active'|'expired'|'destroyed|'error'
     *
     * @var    string
     * @see    getState()
     * @since  2.0
     */
    protected $_state = 'active';

    /**
     * Maximum age of unused session in minutes
     *
     * @var    string
     * @since  2.0
     */
    protected $_expire = 15;

    /**
     * The session store object.
     *
     * @var    BSessionStorage
     * @since  2.0
     */
    protected $_store = null;

    /**
     * Security policy.
     * List of checks that will be done.
     *
     * Default values:
     * - fix_browser
     * - fix_adress
     *
     * @var array
     * @since  2.0
     */
    protected $_security = array('fix_browser');

    /**
     * Force cookies to be SSL only
     * Default  false
     *
     * @var    boolean
     * @since  2.0
     */
    protected $_force_ssl = false;

    /**
     * @var    BSession  BSession instances container.
     * @since  11.3
     */
    protected static $instance;

    /**
     * Constructor
     *
     * @param   string  $store    The type of storage for the session.
     * @param   array   $options  Optional parameters
     *
     * @since   2.0
     */
    public function __construct($store = 'none', $options = array())
     {
        // Need to destroy any existing sessions started with session.auto_start
        if (session_id()) {
            session_unset();
            session_destroy();
        }

        // set default sessios save handler
        ini_set('session.save_handler', 'files');

        // disable transparent sid support
        ini_set('session.use_trans_sid', '0');

        // create handler
        $this->_store = BSessionStorage::getInstance($store, $options);
        #var_dump($this->_store);

        // set options
        $this->_setOptions($options);

        $this->_setCookieParams();

        // load the session
        $this->_start();

        // initialise the session
        $this->_setCounter();
        $this->_setTimers();

        $this->_state = 'active';

        // perform security checks
        $this->_validate();


    }

    /**
     * Session object destructor
     *
     * @since   2.0
     */
    public function __destruct()
     {
        $this->close();
    }

    /**
     * Returns the global Session object, only creating it
     * if it doesn't already exist.
     *
     * @param   string  $handler  The type of session handler.
     * @param   array   $options  An array of configuration options.
     *
     * @return  JSession  The Session object.
     *
     * @since   2.0
     */
    public static function getInstance($handler, $options)
     {
        if (!is_object(self::$instance)) {

            self::$instance = new BSession($handler, $options);
        }

        return self::$instance;
    }

    /**
     * Get current state of session
     *
     * @return  string  The session state
     *
     * @since   2.0
     */
    public function getState()
     {
        return $this->_state;
    }

    /**
     * Get expiration time in minutes
     *
     * @return  integer  The session expiration time in minutes
     *
     * @since   2.0
     */
    public function getExpire()
     {
        return $this->_expire;
    }

    /**
     * Get session name
     *
     * @return  string  The session name
     *
     * @since   2.0
     */
    public function getName()
     {
        if ($this->_state === 'destroyed') {
            // @TODO : raise error
            return null;
        }
        return session_name();
    }

    /**
     * Get session id
     *
     * @return  string  The session name
     *
     * @since   2.0
     */
    public function getId()
     {
        if ($this->_state === 'destroyed') {
            // @TODO : raise error
            return null;
        }
        return session_id();
    }

    /**
     * Get the session handlers
     *
     * @return  array  An array of available session handlers
     *
     * @since   2.0
     */
    public static function getStores()
     {
        BLoader::import('joomla.filesystem.folder');
        $handlers = JFolder::files(dirname(__FILE__) . '/storage', '.inc.php$');

        $names = array();
        foreach ($handlers as $handler) {
            $name = substr($handler, 0, strrpos($handler, '.'));
            $class = 'BSessionStorage' . ucfirst($name);

            //Load the class only if needed
            if (!class_exists($class))
{
                require_once dirname(__FILE__) . '/storage/' . $name . '.inc.php';
            }

            if (call_user_func_array(array(trim($class), 'test'), array()))
{
                $names[] = $name;
            }
        }

        return $names;
    }

    /**
     * Check whether this session is currently created
     *
     * @return  boolean  True on success.
     *
     * @since   2.0
     */
    public function isNew()
     {
        $counter = $this->get('session.counter');
        if ($counter === 1) {
            return true;
        }
        return false;
    }

    /**
     * Get data from the session store
     *
     * @param   string  $name       Name of a variable
     * @param   mixed   $default    Default value of a variable if not set
     * @param   string  $namespace  Namespace to use, default to 'default'
     *
     * @return  mixed  Value of a variable
     *
     * @since   2.0
     */
    public function get($name, $default = null, $namespace = 'default')
     {
        $namespace = '__' . $namespace; //add prefix to namespace to avoid collisions

        if ($this->_state !== 'active' && $this->_state !== 'expired') {
            // @TODO :: generated error here
            $error = null;
            return $error;
        }

        if (isset($_SESSION[$namespace][$name])) {
          if (!is_object ($_SESSION[$namespace][$name]) && gettype ($_SESSION[$namespace][$name]) == 'object')
            return ($_SESSION[$namespace][$name] = unserialize (serialize ($_SESSION[$namespace][$name])));
            return $_SESSION[$namespace][$name];
        }
        return $default;
    }

    /**
     * Set data into the session store.
     *
     * @param   string  $name       Name of a variable.
     * @param   mixed   $value      Value of a variable.
     * @param   string  $namespace  Namespace to use, default to 'default'.
     *
     * @return  mixed  Old value of a variable.
     *
     * @since   2.0
     */
    public function set($name, $value = null, $namespace = 'default')
     {
        $namespace = '__' . $namespace; //add prefix to namespace to avoid collisions

        if ($this->_state !== 'active') {
            // @TODO :: generated error here
            return null;
        }

        $old = isset($_SESSION[$namespace][$name]) ? $_SESSION[$namespace][$name] : null;

        if (null === $value) {
            unset($_SESSION[$namespace][$name]);
        } else {
            $_SESSION[$namespace][$name] = $value;
        }

        return $old;
    }

    /**
     * Check whether data exists in the session store
     *
     * @param   string  $name       Name of variable
     * @param   string  $namespace  Namespace to use, default to 'default'
     *
     * @return  boolean  True if the variable exists
     *
     * @since   2.0
     */
    public function has($name, $namespace = 'default')
     {
        // Add prefix to namespace to avoid collisions.
        $namespace = '__' . $namespace;

        if ($this->_state !== 'active') {
            // @TODO :: generated error here
            return null;
        }

        return isset($_SESSION[$namespace][$name]);
    }

    /**
     * Unset data from the session store
     *
     * @param   string  $name       Name of variable
     * @param   string  $namespace  Namespace to use, default to 'default'
     *
     * @return  mixed   The value from session or NULL if not set
     *
     * @since   2.0
     */
    public function clear($name, $namespace = 'default')
     {
        // Add prefix to namespace to avoid collisions
        $namespace = '__' . $namespace;

        if ($this->_state !== 'active') {
            // @TODO :: generated error here
            return null;
        }

        $value = null;
        if (isset($_SESSION[$namespace][$name])) {
            $value = $_SESSION[$namespace][$name];
            unset($_SESSION[$namespace][$name]);
        }

        return $value;
    }

    /**
     * Start a session.
     *
     * Creates a session (or resumes the current one based on the state of the session)
     *
     * @return  boolean  true on success
     *
     * @since   2.0
     */
    protected function _start()
    {
        // Start session if not started
        if ($this->_state == 'restart') {
            session_id($this->_createId());
        } else {
            $session_name = session_name();
            $oInput = new BInput();
            if (!$oInput->Cookie->get($session_name, false)) {
                if ($oInput->Cookie->get($session_name)) {
                    session_id($oInput->get($session_name));
                    setcookie($session_name, '', time() - 3600);
                }
            }
        }

        session_cache_limiter('none');
        session_start();

        return true;
    }

    /**
     * Frees all session variables and destroys all data registered to a session
     *
     * This method resets the $_SESSION variable and destroys all of the data associated
     * with the current session in its storage (file or DB). It forces new session to be
     * started after this method is called. It does not unset the session cookie.
     *
     * @return  boolean  True on success
     *
     * @see     session_destroy()
     * @see     session_unset()
     * @since   2.0
     */
    public function destroy()
     {
        // Session was already destroyed
        if ($this->_state === 'destroyed') {
            return true;
        }

        // In order to kill the session altogether, such as to log the user out, the session id
        // must also be unset. If a cookie is used to propagate the session id (default behavior),
        // then the session cookie must be deleted.
        if (isset($_COOKIE[session_name()])) {
            $cookie_domain = BSettings::f('default.cookie_domain', '');
            $cookie_path = BSettings::f('default.cookie_path', '/');
            setcookie(session_name(), '', time() - 42000, $cookie_path, $cookie_domain);
        }

        session_unset();
        session_destroy();

        $this->_state = 'destroyed';
        return true;
    }

    /**
     * Restart an expired or locked session.
     *
     * @return  boolean  True on success
     *
     * @see     destroy
     * @since   2.0
     */
    public function restart()
     {
        $this->destroy();
        if ($this->_state !== 'destroyed') {
            // @TODO :: generated error here
            return false;
        }

        // Re-register the session handler after a session has been destroyed, to avoid PHP bug
        $this->_store->register();

        $this->_state = 'restart';
        //regenerate session id
        $id = $this->_createId();
        session_id($id);
        $this->_start();
        $this->_state = 'active';

        $this->_validate();
        $this->_setCounter();

        return true;
    }

    /**
     * Create a new session and copy variables from the old one
     *
     * @return  boolean $result true on success
     *
     * @since   2.0
     */
    public function fork()
     {
        if ($this->_state !== 'active') {
            // @TODO :: generated error here
            return false;
        }

        // Save values
        $values = $_SESSION;

        // Keep session config
        $trans = ini_get('session.use_trans_sid');
        if ($trans) {
            ini_set('session.use_trans_sid', 0);
        }
        $cookie = session_get_cookie_params();

        // Create new session id
        $id = $this->_createId();

        // Kill session
        session_destroy();

        // Re-register the session store after a session has been destroyed, to avoid PHP bug
        $this->_store->register();

        // restore config
        ini_set('session.use_trans_sid', $trans);
        session_set_cookie_params($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure']);

        // restart session with new id
        session_id($id);
        session_start();

        return true;
    }

    /**
     * Writes session data and ends session
     *
     * Session data is usually stored after your script terminated without the need
     * to call JSession::close(), but as session data is locked to prevent concurrent
     * writes only one script may operate on a session at any time. When using
     * framesets together with sessions you will experience the frames loading one
     * by one due to this locking. You can reduce the time needed to load all the
     * frames by ending the session as soon as all changes to session variables are
     * done.
     *
     * @return  void
     *
     * @see     session_write_close()
     * @since   2.0
     */
    public function close()
     {
        session_write_close();
    }

    /**
     * Create a session id
     *
     * @return  string  Session ID
     *
     * @since   2.0
     */
    protected function _createId()
     {
        $id = 0;
        while (strlen($id) < 32) {
            $id .= mt_rand(0, mt_getrandmax());
        }

        $id = md5(uniqid($id, true));
        return $id;
    }

    /**
     * Set session cookie parameters
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _setCookieParams()
     {
        $cookie = session_get_cookie_params();
        if ($this->_force_ssl) {
            $cookie['secure'] = true;
        }

        if (BSettings::f('cookie_domain', '') != '') {
            $cookie['domain'] = BSettings::f('cookie_domain');
        }

        if (BSettings::f('cookie_path', '') != '') {
            $cookie['path'] = BSettings::f('cookie_path');
        }
        session_set_cookie_params($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure']);
    }

    /**
     * Set counter of session usage
     *
     * @return  boolean  True on success
     *
     * @since   2.0
     */
    protected function _setCounter()
     {
        $counter = $this->get('session.counter', 0);
        ++$counter;

        $this->set('session.counter', $counter);
        return true;
    }

    /**
     * Set the session timers
     *
     * @return  boolean  True on success
     *
     * @since   2.0
     */
    protected function _setTimers()
     {
        if (!$this->has('session.timer.start')) {
            $start = time();

            $this->set('session.timer.start', $start);
            $this->set('session.timer.last', $start);
            $this->set('session.timer.now', $start);
        }

        $this->set('session.timer.last', $this->get('session.timer.now'));
        $this->set('session.timer.now', time());

        return true;
    }

    /**
     * Set additional session options
     *
     * @param   array  &$options  List of parameter
     *
     * @return  boolean  True on success
     *
     * @since   2.0
     */
    protected function _setOptions(&$options)
     {
        // Set name
        if (isset($options['name'])) {
            session_name(md5($options['name']));
        }

        // Set id
        if (isset($options['id'])) {
            session_id($options['id']);
        }

        // Set expire time
        if (isset($options['expire'])) {
            $this->_expire = $options['expire'];
        }

        // Get security options
        if (isset($options['security'])) {
            $this->_security = explode(',', $options['security']);
        }

        if (isset($options['force_ssl'])) {
            $this->_force_ssl = (bool) $options['force_ssl'];
        }

        // Sync the session maxlifetime
        ini_set('session.gc_maxlifetime', $this->_expire);

        return true;
    }

    /**
     * Do some checks for security reason
     *
     * - timeout check (expire)
     * - ip-fixiation
     * - browser-fixiation
     *
     * If one check failed, session data has to be cleaned.
     *
     * @param   boolean  $restart  Reactivate session
     *
     * @return  boolean  True on success
     *
     * @see     http://shiflett.org/articles/the-truth-about-sessions
     * @since   2.0
     */
    protected function _validate($restart = false)
     {
        // Allow to restart a session
        if ($restart) {
            $this->_state = 'active';

            $this->set('session.client.address', null);
            $this->set('session.client.forwarded', null);
            $this->set('session.client.browser', null);
            $this->set('session.token', null);
        }

        // Check if session has expired
        if ($this->_expire) {
            $curTime = $this->get('session.timer.now', 0);
            $maxTime = $this->get('session.timer.last', 0) + $this->_expire;

            // Empty session variables
            if ($maxTime < $curTime) {
                $this->_state = 'expired';
                return false;
            }
        }

        // Record proxy forwarded for in the session in case we need it later
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $this->set('session.client.forwarded', $_SERVER['HTTP_X_FORWARDED_FOR']);
        }

        // Check for client address
        if (in_array('fix_adress', $this->_security) && isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $this->get('session.client.address');

            if ($ip === null) {
                $this->set('session.client.address', $_SERVER['REMOTE_ADDR']);
            } elseif ($_SERVER['REMOTE_ADDR'] !== $ip) {
                $this->_state = 'error';
                return false;
            }
        }

        // Check for clients browser
        if (in_array('fix_browser', $this->_security) && isset($_SERVER['HTTP_USER_AGENT'])) {
            $browser = $this->get('session.client.browser');

            if ($browser === null) {
                $this->set('session.client.browser', $_SERVER['HTTP_USER_AGENT']);
            } elseif ($_SERVER['HTTP_USER_AGENT'] !== $browser) {
                //                $this->_state    =    'error';
                //                return false;
            }
        }

        return true;
    }
}
