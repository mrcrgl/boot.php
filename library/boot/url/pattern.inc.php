<?php

class BUrlPattern
{
    
    public $expression = null;
    
    public $destination = null;
    
    public function __construct($sExpression, $mDestination)
    {
        $this->expression = $sExpression;
        $this->destination = $mDestination;
    }
    
    /**
     * Get, if exists, the dest component.
     *
     * @param string $sDestination The pattern destination.
     *
     * @return string if destination not a component, false
     */
    public function getDestinationComponent()
    {
        $sDestination = $this->splitDestination();
        if (substr($sDestination, 0, strlen('include:')) == 'include:') {
            return substr($sDestination, strlen('include:'));
        }
        return false;
    }
    
    /**
     * Splits, if the destination is an array, the values to dest and args.
     *
     * @param string  $sDestination The pattern destination.
     * @param boolean $bPublishArgs If true, sends args to GET (Default:true).
     *
     * @return string The real destination.
     */
    public function splitDestination($bPublishArgs=true)
    {
        if (!class_exists('Validator'))
            BLoader::import('boot.utilities.validator');
    
        if (Validator::is($this->destination, 'array')) {
            $temp = $this->destination;
            $args = array();
            if (isset($temp[1])) $args = $temp[1];
            $sDestination = $temp[0];
            if ($bPublishArgs) {
                foreach ($args as $argk => $argv) {
                    $_GET[$argk] = $argv;
                }
            }
        } else {
            $sDestination = $this->destination;
        }
        return $sDestination;
    }
}