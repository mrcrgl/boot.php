<?php
/**
 * The BComponent class.
 *
 * @license   MIT Licence (see LICENSE file)
 * @copyright 2012 Marc Riegel
 *
 * @author  Marc Riegel <mail@marclab.de>
 * @package boot.php.library
 * @subpackage Url
 */

/**
 * The BComponent class.
 *
 * @author Marc Riegel <mail@marclab.de>
 */
class BComponent extends BObject
{
    
    /*
        Path of component
        Template Path if available
        View Path if available
        Ref: Urls
        Ref: Controller
        Ref: Config
    */
    
    protected $_bIsComponent = false;
    protected $_bExecute = false;
    
    protected $_sPath = null;
    protected $_sTemplatePath = null;
    protected $_sModelPath = null;
    protected $_sViewPath = null;
    
    protected $_sIdentificator = null;
    
    protected $_oConfig = null;
    protected $_oUrl = null;
    protected $_oController = null;
    
    protected $_sRequestView = null;
    protected $_sRequestMethod = null;
    
    public function getUrl()
    {
        if (!$this->isValid()) throw new Exception("Try to get BUrl from invalid BComponent.");
        
        if (null === $this->get('_oUrl', null)) {
            
            if (file_exists($this->get('_oUrl').DS.'urls.inc.php')) {
                $sClassname = sprintf('Component%sUrls', BString::underscores_to_camelcase($this->get('_sIdentificator')));
                $this->set('_oUrl', new $sClassname());
            }
            
        }
        
        return $this->get('_oUrl');
    }
    
    public function getController()
    {
        if (null === $this->get('_oController', null)) {
        
            $sClassname = sprintf('Component%sController', BString::underscores_to_camelcase($this->get('_sIdentificator')));
        
            $this->set('_oController', new $sClassname());
        }
        
        return $this->get('_oController');
    }
    
    public function callController()
    {
        $oController = $this->getController();
        $oController->oComponent =& $this;
        //$oController->setRequestView($this->getRequestView());
        //$oController->setRequestMethod($this->getRequestMethod());
        $oController->register();
        //var_dump($this);
        //var_dump($this->get('_bExecute', false));
        
        if ($this->get('_bExecute', false) == true) {
            $oController->handleRequest();
        }
    }
    
    public function getPath()
    {
        return $this->get('_sPath', null);
    }
    
    public function getTemplatePath()
    {
        return $this->get('_sTemplatePath', null);
    }
    
    public function getModelPath()
    {
        return $this->get('_sModelPath', null);
    }
    
    public function getViewPath()
    {
        return $this->get('_sViewPath', null);
    }
    
    public function setRequestView($sView)
    {
        return $this->set('_sRequestView', $sView);
    }
    
    public function getRequestView()
    {
        return $this->get('_sRequestView', null);
    }
    
    public function setRequestMethod($sMethod)
    {
        return $this->set('_sRequestMethod', $sMethod);
    }
    
    public function getRequestMethod()
    {
        return $this->set('_sRequestMethod', null);
    }
    
    public function getViewClassname($sViewIdent)
    {
        return sprintf("Component%sView%s", ucfirst($this->get('_sIdentificator')), ucfirst($sViewIdent));
    }
    
    public function isValid()
    {
        return $this->get('_bIsComponent');
    }
}