<?php
/**
 * The BUrl class.
 *
 * @license   MIT Licence (see LICENSE file)
 * @copyright 2012 Marc Riegel
 *
 * @author  Marc Riegel <mail@marclab.de>
 * @package boot.php.library
 * @subpackage Url
 */

/**
 * The BUrl class.
 *
 * @name BUrl
 */
class BUrl
{

    /**
     * The global BUrl instance.
     *
     * Default: null
     *
     * @var object
     */
    static $oInstance = null;

    /**
     * Overwritten by UrlConfig.
     *
     * @var array
     */
    public $pattern = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        // DEPRECATED
        /*if (get_class($this) == 'BUrl'
            && is_file(PROJECT_CONFIG.DS.'urls.inc.php')) {
            BLoader::register('ProjectUrls', PROJECT_CONFIG.DS.'urls.inc.php');
            $oProjectUrls = new ProjectUrls();
            $this->register($oProjectUrls->getPattern());
        }*/
    }

    /**
     * Get current Instance.
     *
     * @return object
     */
    public static function getInstance()
    {
        if (!is_object(self::$oInstance)) {

            #self::$oInstance = new BUrl();
            BLoader::register('ProjectUrls', PROJECT_CONFIG.DS.'urls.inc.php');
            self::$oInstance = new ProjectUrls();
        }

        return self::$oInstance;
    }

    

    /**
     * The big funky method, that do all for us.
     *
     * @param string $sRequestUri The request URI.
     * @param string $sChainedUri Already chained part.
     *
     * @todo Reorganize this method.
     * @return boolean true if successful, otherwise false
     */
    public function parse($sRequestUri=null, $sChainedUri=null)
    {

        if (!class_exists('BArray'))
            BLoader::import('boot.utilities.array');

        if ($sRequestUri === null) {
            $oInput =& BFactory::getInput();
            $sRequestUri = $oInput->get('REQUEST_URI', '/', 'server');
        }
        
        $sParsedUrl = parse_url($sRequestUri);
        $sPath = implode(
            '/',
            BArray::strip_empty_values(
                explode('/', $sParsedUrl['path']),
                true
            )
        );

        $sPath = $this->checkTrailingSlash($sPath);

        if (!$sChainedUri) $sChainedUri = $sPath;

        $aPatternList =& $this->getPattern();

        foreach ($aPatternList as $sPattern => $sDestination) {
            $sPattern = str_replace('/', '\/', $sPattern);
            $epattern = '/'.$sPattern.'/';

            if (!preg_match($epattern, $sPath, $aMatches, PREG_OFFSET_CAPTURE)) {
                // This one does not match. Next one.
                continue;
            }
            
            foreach ($aMatches as $key => $match) {
                if (!is_numeric($key)) {
                    // TODO: Check what it is.
                    $_GET[$key] = $match[0];
                }
            }
            
            $oUrlPattern = new BUrlPattern($sPattern, $sDestination);
            
            #print $sDestination.NL;
            
            $sDestination    = $oUrlPattern->splitDestination();
            $sComponentIdent = $oUrlPattern->getComponent();
            
            #print "Destination: ".$sComponentIdent.NL;
            
            $oComponent = BComponentFactory::getInstance($sComponentIdent);
            
            $oComponent->setRequestView($oUrlPattern->getView());
            $oComponent->setRequestMethod($oUrlPattern->getMethod());
            
            // Register template path to renderer.
            // TODO: This must be delegated. Document and Renderer has not to be here.
            $oDocument =& BFactory::getDocument();
            $oRenderer =& $oDocument->getRenderer();
            
            // If class is not
            /*if (!substr($sComponentPath, 0, strlen(PROJECT_ROOT)) == PROJECT_ROOT) {
                $sComponentPath = PROJECT_ROOT;
            }*/
            
            // TODO: Move to BComponent
            #print $oComponent->getTemplatePath().NL;
            $oRenderer->unshiftTemplateDir($oComponent->getTemplatePath());
            
            /*printf(
                "--".NL.
                "Component: %s".NL.
                "View: %s".NL.
                "Method: %s".NL.NL,
                $oComponent->get('_sIdentificator'),
                $oComponent->getRequestView(),
                $oComponent->getRequestMethod()
            );*/
            
            if (!$oComponent->isValid()) {
                printf("Component %s is invalid.", $oComponent->get('sIdentificator'));
                return false;
            }
            
            BComponentLeader::append($oComponent);
            
            // Found, next level.
            if (!$oComponent->hasDestination()) {
                // There is another Component linked.
                $sMatchedPart = $aMatches[0][0];

                $sRemainingPart = str_replace($sMatchedPart, '', $sPath);

                
                
                
                $oComponentUrl = $oComponent->getUrl();
                
                #var_dump($oComponent);
                #var_dump($oComponentUrl);
                
                $sRemainingPart = $this->checkTrailingSlash($sRemainingPart);

                // Url prefix to able the component building urls.
                if (substr($sChainedUri, '-'.strlen($sRemainingPart)) == $sRemainingPart) {
                    $oDocument->setUrlPrefix(
                        substr(
                            $sChainedUri,
                            0,
                            strlen($sChainedUri)-(strlen($sRemainingPart))
                        )
                    );
                } else {
                    $oDocument->setUrlPrefix($sChainedUri);
                }
                
                #if (null === $oComponentUrl) return true;
                
                return $oComponentUrl->parse(
                    $sRemainingPart,
                    $sChainedUri
                );

            } else {
                
                // All fine.
                return true;
            }//end if


        }//end foreach

        // When we are here, no file is found. Throw 404.
        BResponse::error(404, "No Route to component.");

        return false;
    }
    
    /*private function getClassPath()
    {
        $oReflection = new ReflectionObject($this);
        $sClassPath = dirname($oReflection->getFileName());
        unset($oReflection);
        return $sClassPath;
    }*/

    private function checkTrailingSlash($sPath)
    {
        if (substr($sPath, -1) != '/') {
            $sPath = $sPath.'/';
        }
        return $sPath;
    }
    /**
     * Old parse function.
     *
     * @param string $url The request uri.
     *
     * @deprecated since 2.0
     * @return void
     */
    /*static public function _parse($url=null)
    {

        BLoader::import('boot.utilities.array');

        if (!$url) {
            $oInput =& BFactory::getInput();
            $url   = $oInput->get('REQUEST_URI', 'index', 'server');
        }

        $sParsedUrl = parse_url($url);
        $aPathParts = BArray::strip_empty_values(
            explode('/', $sParsedUrl['path']),
            true
        );

        $_GET['_vc'] = BArray::get($aPathParts, 0);
        $_GET['_vv'] = BArray::get($aPathParts, 1);
        $_GET['_vm'] = BArray::get($aPathParts, 2);

    }*/

    /**
     * Register a expression/destination pair.
     *
     * @param mixed $expression   The expression.
     * @param mixed $sDestination The destination.
     *
     * @return null
     */
    public function register($expression, $sDestination=null)
    {
        if (!class_exists('Validator'))
            BLoader::import('boot.utilities.validator');

        if (Validator::is($expression, 'array')) {
            foreach ($expression as $sPattern => $dest) {
                $this->register($sPattern, $dest);
            }
            return null;
        }

        $this->pattern[$expression] = $sDestination;

        return null;
    }

    /**
     * Getter for pattern.
     *
     * @return array Array of pattern (expression => destination)
     */
    function getPattern()
    {
        return $this->pattern;
    }

}