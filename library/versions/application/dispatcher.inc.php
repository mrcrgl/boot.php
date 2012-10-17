<?php
/**
 * The topmost Dispatcher
 *
 * PHP version 5
 *
 * @category  Application
 * @package   Versions.Library
 *
 * @author    Marc Riegel <mail@marclab.de>
 * @copyright 2012 - now Marc Riegel
 * @version   Release: $Id$
 * @link      none
 */
VLoader::register(
    'Validator',
    VLIB.DS.'versions'.DS.
    'utilities'.DS.'validator.inc.php'
);
VLoader::discover(dirname(__FILE__).DS.'dispatcher');

/**
 * VDispatcher.
 *
 * @category  Application
 * @package   Versions.Library
 *
 * @author    Marc Riegel <mail@marclab.de>
 * @copyright 2012 - now Marc Riegel
 * @version   SVN: $Id$
 * @license   none
 * @link      none
 */
class VDispatcher
{

    /**
     * Stores the current Dispatcher instance
     *
     * @var $oDispatcher VDispatcher
     */
    var $oDispatcher = null;

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        if (is_null($this->oDispatcher)) {
            $this->oDispatcher = $this->_getInstance(
                VSettings::f('application.dispatcher', 'default')
            );
        }

    }

    /**
     * Get VDispatcher instance of given type
     *
     * @param string $sType
     * @throws Exception
     */
    private function _getInstance($sType='default')
    {

        $sClassname = 'VDispatcher'.ucfirst($sType);

        if (!class_exists($sClassname)) {
            $sMessage = sprintf(
                'Dispatcher %s not found. Exiting...',
                $sClassname
            );
            throw new Exception($sMessage);
            //user_error()
        }

        return new $sClassname();
    }

    /**
     * Magic function __call, dont know why?!
     *
     * @param string    $sMethod
     * @param array     $aArgs
     */
    public function __call($sMethod, $aArgs)
    {
        return call_user_func_array(
            array($this->oDispatcher, $sMethod),
            $aArgs
        );
    }

    /**
     * Unused function, or?
     *
     * @param string  $method
     * @param array   $args
     * @throws Exception
     */
    static public function __callStatic($method, $args)
    {
        throw new Exception('Dispatcher: __callStatic not implemented yet.');
    }
}