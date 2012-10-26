<?php
/**
 * The BComponentFactory class.
 *
 * @license   MIT Licence (see LICENSE file)
 * @copyright 2012 Marc Riegel
 *
 * @author  Marc Riegel <mail@marclab.de>
 * @package boot.php.library
 * @subpackage Url
 */

/**
 * The BComponentFactory class.
 *
 * @author Marc Riegel <mail@marclab.de>
 */
class BComponentFactory extends BObject
{
    /*
        Fetching Components
        Maybe, integrate core functions of BUrls
    */
    
    static public function getInstance($sComponentPath)
    {
        $oComponent = new BComponent();
        
        if (!is_dir($sComponentPath)) {
            $sComponentPath = self::getPathByIdentificator($sComponentPath);
        }
        
        $oComponent->set('_sIdentificator', self::getIdentificator($sComponentPath));
        $oComponent->set('_bIsComponent', self::isComponentPath($sComponentPath));
        
        self::preparePaths(&$oComponent, $sComponentPath);
        
        return $oComponent;
    }
    
    static function getPathByIdentificator($sIdentificator)
    {
        if (is_dir(PROJECT_COMPONENTS.DS.$sIdentificator)) {
            return PROJECT_COMPONENTS.DS.$sIdentificator;
        }
        if (is_dir(VCOMPONENTS.DS.$sIdentificator)) {
            return VCOMPONENTS.DS.$sIdentificator;
        }
        throw new Exception(sprintf(BText::_('Identification \'%s\' not found in component folders.')));
    }
    
    static function preparePaths(BComponent $oComponent, $sComponentPath)
    {
        if (!$oComponent->get('_bIsComponent')) {
            $sComponentPath = PROJECT_ROOT;
        }
        
        $oComponent->set('_sPath', $sComponentPath);
    
        if (is_dir($sComponentPath.DS.'templates')) {
            $oComponent->set('_sTemplatePath', $sComponentPath.DS.'templates');
        }
    
        if (is_dir($sComponentPath.DS.'models')) {
            $oComponent->set('_sModelPath', $sComponentPath.DS.'models');
        }
    
        if (is_dir($sComponentPath.DS.'views')) {
            $oComponent->set('_sViewPath', $sComponentPath.DS.'views');
        }
    
    }
    
    static function getIdentificator($sComponentPath)
    {
        $sComponentPath = realpath($sComponentPath);
        
        
        if ($sComponentPath == PROJECT_COMPONENTS) {
            return false;
        }
        
        if (substr($sComponentPath, 0, strlen(PROJECT_COMPONENTS)) == PROJECT_COMPONENTS) {
            return str_replace('/', '', str_replace(realpath(PROJECT_COMPONENTS), '', $sComponentPath));
        }
        if (substr($sComponentPath, 0, strlen(VCOMPONENTS)) == VCOMPONENTS) {
            return str_replace('/', '', str_replace(realpath(VCOMPONENTS), '', $sComponentPath));
        }
        return false;
    }
    
    static function isComponentPath($sComponentPath)
    {
        if (false !== self::getIdentificator($sComponentPath)) {
            return true;
        }
        return false;
    }
}