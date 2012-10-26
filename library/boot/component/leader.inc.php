<?php
/**
 * The BComponentLeader class.
 *
 * @license   MIT Licence (see LICENSE file)
 * @copyright 2012 Marc Riegel
 *
 * @author  Marc Riegel <mail@marclab.de>
 * @package boot.php.library
 * @subpackage Url
 */

/**
 * The BComponentLeader class.
 *
 * @author Marc Riegel <mail@marclab.de>
 */
class BComponentLeader extends BObject
{
    /*
        Stores one or many Components to walk through
    */
    
    static $aChainedComponents = array();
    
    /**
     * Method description.
     *
     * @return mixed next BComponent or false
     */
    static public function walk()
    {
        $a = current(self::$aChainedComponents);
        next(self::$aChainedComponents);
        return $a;
    }
    
    /**
     * Method description.
     *
     * @return mixed the value of the first array element, or false if the array is empty.
     */
    static public function reset()
    {
        return reset(self::$aChainedComponents);
    }
    
    /**
     * Method description.
     *
     * @return integer Count of assigned Components
     */
    static public function count()
    {
        return count(self::$aChainedComponents);
    }
    
    /**
     * Method description.
     *
     * @param BComponent $oComponent Component to append.
     *
     * @return integer The new count of elements.
     */
    static public function append(BComponent $oComponent)
    {
        return array_push(self::$aChainedComponents, $oComponent);
    }
    
    static public function isLast()
    {
        #$tmp = array_keys(self::$aChainedComponents, current(self::$aChainedComponents));
        $current = key(self::$aChainedComponents);
        /*print "<pre>";
        printf("Count %s".NL, print_r(self::$aChainedComponents, true));
        print "is last $current:";
        var_dump(isset(self::$aChainedComponents[($current+1)]) ? false : true);*/
        return isset(self::$aChainedComponents[($current)]) ? false : true;
    }
}