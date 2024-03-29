<?php
/**
 * XCache session storage handler
 *
 * @package     boot.php.core
 * @subpackage  Cache
 * @since       11.1
 */
class BSessionStorageXcache extends BSessionStorage
{
    /**
     * Constructor
     *
     * @param   array  $options  Optional parameters.
     *
     * @since   11.1
     */
    public function __construct($options = array())
     {
        if (!$this->test()) {
            BResponse::error(404);
        }

        parent::__construct($options);
    }

    /**
     * Open the SessionHandler backend.
     *
     * @param   string  $save_path     The path to the session object.
     * @param   string  $session_name  The name of the session.
     *
     * @return  boolean  True on success, false otherwise.
     *
     * @since   11.1
     */
    public function open($save_path, $session_name)
     {
        return true;
    }

    /**
     * Close the SessionHandler backend.
     *
     * @return boolean  True on success, false otherwise.
     */
    public function close()
     {
        return true;
    }

    /**
     * Read the data for a particular session identifier from the SessionHandler backend.
     *
     * @param   string  $id  The session identifier.
     *
     * @return  string  The session data.
     *
     * @since   11.1
     */
    public function read($id)
     {
        $sess_id = 'sess_' . $id;

        // Check if id exists
        if (!xcache_isset($sess_id)) {
            return;
        }

        return (string) xcache_get($sess_id);
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
        $sess_id = 'sess_' . $id;
        return xcache_set($sess_id, $session_data, ini_get("session.gc_maxlifetime"));
    }

    /**
     * Destroy the data for a particular session identifier in the SessionHandler backend.
     *
     * @param   string  $id  The session identifier.
     *
     * @return  boolean  True on success, false otherwise.
     *
     * @since   11.1
     */
    public function destroy($id)
     {
        $sess_id = 'sess_' . $id;

        if (!xcache_isset($sess_id)) {
            return true;
        }

        return xcache_unset($sess_id);
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
     * @return boolean  True on success, false otherwise.
     */
    static public function test()
     {
        return (extension_loaded('xcache'));
    }
}
