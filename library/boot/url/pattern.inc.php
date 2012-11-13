<?php

class BUrlPattern
{
    
    public $sExpression = null;
    
    public $sDestination = null;
    
    public $sComponent = null;
    
    public $sView = null;
    
    public $sMethod = null;
    
    public function __construct($sExpression, $mDestination)
    {
        $this->sExpression = $sExpression;
        $this->sDestination = $mDestination;
        list($this->sComponent, $this->sView, $this->sMethod) = $this->decodeDestimnation();
    }
    
    /**
     * Get component.
     *
     * @return string component
     */
    public function getComponent()
    {
        return $this->sComponent;
    }
    
    /**
     * Get view.
     *
     * @return string view
     */
    public function getView()
    {
        return $this->sView;
    }
    
    /**
     * Get method.
     *
     * @return string method
     */
    public function getMethod()
    {
        return $this->sMethod;
    }
    
    /**
     * Get, if exists, the dest component.
     *
     * @return array (component, view, method)
     */
    public function decodeDestimnation()
    {
        $sDestination = $this->splitDestination();
        if (substr($sDestination, 0, strlen('include:')) == 'include:') {
            $sComponent = substr($sDestination, strlen('include:'));
            return array($sComponent, null, null);
        }
        if (preg_match('/^([a-z]+)\.([a-z]+)\.([a-z]+)$/', $sDestination, $matches)) {
            #var_dump($matches);
            return array($matches[1], $matches[2], $matches[3]);
        }
        return array(null, null, null);
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
    
        if (Validator::is($this->sDestination, 'array')) {
            $temp = $this->sDestination;
            $args = array();
            if (isset($temp[1])) $args = $temp[1];
            $sDestination = $temp[0];
            if ($bPublishArgs) {
                foreach ($args as $argk => $argv) {
                    $_GET[$argk] = $argv;
                }
            }
        } else {
            $sDestination = $this->sDestination;
        }
        return $sDestination;
    }
}