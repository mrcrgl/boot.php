<?php
/**
 * The topmost Dispatcher
 *
 * PHP version 5
 *
 * @category  Application
 * @package   boot.php.Library
 *
 * @author    Marc Riegel <mail@marclab.de>
 * @copyright 2012 - now Marc Riegel
 * @version   Release: $Id$
 * @link      none
 */
BLoader::register(
    'Validator',
    BLIB.DS.'boot'.DS.
    'utilities'.DS.'validator.inc.php'
);
BLoader::discover(dirname(__FILE__).DS.'dispatcher');

/**
 * BDispatcher.
 *
 * @category  Application
 * @package   boot.php.Library
 *
 * @author    Marc Riegel <mail@marclab.de>
 * @copyright 2012 - now Marc Riegel
 * @version   SVN: $Id$
 * @license   none
 * @link      none
 */
class BDispatcher
{

    /**
     * Stores the current Dispatcher instance
     *
     * @var $oDispatcher BDispatcher
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
                BSettings::f('application.dispatcher', 'default')
            );
        }

    }

    /**
     * Get BDispatcher instance of given type
     *
     * @param string $sType
     * @throws Exception
     */
    private function _getInstance($sType='default')
     {

        $sClassname = 'BDispatcher'.ucfirst($sType);

        if (!class_exists($sClassname))
{
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