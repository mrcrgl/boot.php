<?php
/**
 * @package     boot.php.core
 * @subpackage  Session
 */

/**
 * Custom session storage handler for PHP
 *
 * @package     boot.php.core
 * @subpackage  Session
 * @see         http://www.php.net/manual/en/function.session-set-save-handler.php
 * @since       11.1
 */
abstract class BSessionStorage extends BObject 
{
    /**
     * @var    array  BSessionStorage instances container.
     * @since  11.3
     */
    protected static $instances = array();

    /**
     * Constructor
     *
     * @param   array  $options  Optional parameters.
     *
     * @since   11.1
     */
    public function __construct($options = array())
     {
        $this->register($options);
    }

    /**
     * Returns a session storage handler object, only creating it if it doesn't already exist.
     *
     * @param   string  $name     The session store to instantiate
     * @param   array   $options  Array of options
     *
     * @return  BSessionStorage
     *
     * @since   2.0
     */
    public static function getInstance($options = array())
     {
        $name = BSettings::f('session.storage', 'none');
        
        if (empty(self::$instances[$name])) {
            $class = 'BSessionStorage' . ucfirst($name);

            if (!class_exists($class)) 
{
                $path = dirname(__FILE__) . '/storage/' . $name . '.inc.php';

                if (file_exists($path)) {
                    require_once $path;
                } else {
                    // No call to BError::raiseError here, as it tries to close the non-existing session
                    // TODO
                    print ('Unable to load session storage class: ' . $name);
                }
            }

            self::$instances[$name] = new $class($options);
        }

        return self::$instances[$name];
    }

    /**
     * Register the functions of this class with PHP's session handler
     *
     * @param   array  $options  Optional parameters
     *
     * @return  void
     *
     * @since   2.0
     */
    public function register($options = array())
     {
        // use this object as the session handler
        session_set_save_handler(
            array($this, 'open'), array($this, 'close'), array($this, 'read'), array($this, 'write'),
            array($this, 'destroy'), array($this, 'gc')
        );
    }

    /**
     * Open the SessionHandler backend.
     *
     * @param   string  $save_path     The path to the session object.
     * @param   string  $session_name  The name of the session.
     *
     * @return  boolean  True on success, false otherwise.
     *
     * @since   2.0
     */
    public function open($save_path, $session_name)
     {
        return true;
    }

    /**
     * Close the SessionHandler backend.
     *
     * @return  boolean  True on success, false otherwise.
     *
     * @since   11.1
     */
    public function close()
     {
        return true;
    }

    /**
     * Read the data for a particular session identifier from the
     * SessionHandler backend.
     *
     * @param   string  $id  The session identifier.
     *
     * @return  string  The session data.
     *
     * @since   11.1
     */
    public function read($id)
     {
        return;
    }

    /**
     * Write session data to the SessionHandler backend.
     *
     * @param   string  $id            The session identifier.
     * @param   string  $session_data  The session data.
     *
     * @return  boolean  True on success, false otherwise.
     *
     * @since   11.1
     */
    public function write($id, $session_data)
     {
        return true;
    }

    /**
     * Destroy the data for a particular session identifier in the
     * SessionHandler backend.
     *
     * @param   string  $id  The session identifier.
     *
     * @return  boolean  True on success, false otherwise.
     *
     * @since   11.1
     */
    public function destroy($id)
     {
        return true;
    }

    /**
     * Garbage collect stale sessions from the SessionHandler backend.
     *
     * @param   integer  $maxlifetime  The maximum age of a session.
     *
     * @return  boolean  True on success, false otherwise.
     *
     * @since   11.1
     */
    public function gc($maxlifetime = null)
     {
        return true;
    }

    /**
     * Test to see if the SessionHandler is available.
     *
     * @return  boolean  True on success, false otherwise.
     *
     * @since   11.1
     */
    public static function test()
     {
        return true;
    }
}
