<?php
/**
 * The VUrl class.
 *
 * @author  Marc Riegel <mail@marclab.de>
 * @package Versions.library
 * @subpackage Url
 */

/**
 * The VUrl class.
 *
 * @name VUrl
 */
class VUrl
{

    static $instance = null;

    var $pattern = array();

    var $_registred = array();

    public function __construct()
    {
        // register global urls?
        #printf("__construct() Class is %s".NL, get_class($this));
        if (get_class($this) == 'VUrl' && is_file(PROJECT_CONFIG.DS.'urls.inc.php'))
        {
            VLoader::register('ProjectUrls', PROJECT_CONFIG.DS.'urls.inc.php');
            $urls = new ProjectUrls();
            $this->register( $urls->getPattern() );

        }

        // loading first urls
        if (get_class($this) == 'ProjectUrls')
        {

        }
    }

    public static function getInstance()
    {
        if (!is_object(self::$instance)) {

            self::$instance = new VUrl();
        }

        return self::$instance;
    }

    public function getDestinationComponent($sDestination)
    {
        if (substr($sDestination, 0, strlen('include:')) == 'include:') {
            return substr($sDestination, strlen('include:'));
        }
        return false;
    }

    public function splitDestination($sDestination, $publish_args=true)
    {
        if (!class_exists('Validator'))
            VLoader::import('versions.utilities.validator');

        if (Validator::is($sDestination, 'array')) {
            $temp = $sDestination;
            $args = array();
            if (isset($temp[1])) $args = $temp[1];
            $sDestination = $temp[0];
            if ($publish_args) {
                foreach ($args as $argk => $argv) {
                    $_GET[$argk] = $argv;
                }
            }
        }
        return $sDestination;
    }

    public function parse($sRequestUri=null, $chained_uri=null)
    {

        if (!class_exists('VArray'))
            VLoader::import('versions.utilities.array');

        if ($sRequestUri === null) {
            $oInput =& VFactory::getInput();
            $sRequestUri   = $oInput->get('REQUEST_URI', '/', 'server');
        }

        $sParsedUrl = parse_url($sRequestUri);
        $path = implode( '/', VArray::strip_empty_values( explode('/', $sParsedUrl['path']), true ) );

        if (substr($path, -1) != '/') {
            $path = $path.'/';
        }

        /*if (substr($path, 0, 1) != '/') {
         $path = '/'.$path;
        }*/

        if (!$chained_uri) $chained_uri = $path;

        #printf("Class is %s".NL, get_class($this));
        #$url =& VFactory::getUrl();
        #$patternlist = (($use_own_pattern) ? $this->getPattern() : $url->getPattern());
        $patternlist = $this->getPattern();

        foreach ($patternlist as $pattern => $sDestination) {
            #printf("Pattern: %s Required: %s".NL, htmlspecialchars($pattern), $path);
            $pattern = str_replace('/', '\/', $pattern);
            $epattern = '/'.$pattern.'/';

            if (preg_match($epattern, $path, $matches, PREG_OFFSET_CAPTURE)) {
                #print "<pre>";var_dump($matches);print "</pre><br />";

                foreach ($matches as $key => $match) {
                if (!is_numeric($key)) {
                    $_GET[$key] = $match[0];
                }
            }

            $sDestination = $this->splitDestination($sDestination);

            /*
             * register template path to renderer
            */
            $oDocument =& VFactory::getDocument();
            $oRenderer =& $oDocument->getRenderer();
            $object = new ReflectionObject($this);
            $template_path = dirname($object->getFileName()).DS.'templates';
            #print "<pre>";

            #print $template_path.NL;
            $oRenderer->unshiftTemplateDir($template_path);
            #var_dump($oRenderer->getTemplateDir());
            #print "</pre>";

            // found, next level
            if ($component_ident = $this->getDestinationComponent($sDestination)) {
                #print "Have to include $component_ident".NL;

                $matched_part = $matches[0][0];
            #printf("Matched part: %s".NL, $matched_part);

            $remaining_part = str_replace($matched_part, '', $path);
            #printf("Continue with part: %s".NL, $remaining_part);

            $url_classname = sprintf("Component%sUrls", ucfirst($component_ident));
            $com_urls = new $url_classname();

            if (substr($remaining_part, -1) != '/') {
                $remaining_part = $remaining_part.'/';
            }

            /*
             * Url prefix to able the component building urls
            */
            #print "Chained: ".$chained_uri.NL;
            #print "Remaining: ".$remaining_part.NL;
            #print(str_replace($remaining_part, '', $chained_uri));

            #print substr($chained_uri, '-'.strlen($remaining_part));
            #printf(NL."Chained Uri: %s contains %s (length %d)".NL, $chained_uri, $remaining_part, strlen($remaining_part));
            if (substr($chained_uri, '-'.strlen($remaining_part)) == $remaining_part) {
                #print "TRUE substr($chained_uri, 0, strlen($remaining_part)) == $remaining_part".NL;
                $oDocument->setUrlPrefix( substr($chained_uri, 0, strlen($chained_uri)-(strlen($remaining_part))) );
            } else {
                $oDocument->setUrlPrefix( $chained_uri );
            }
            #print "URL Prefix: ".$oDocument->getUrlPrefix().NL;
            #print NL;
            #$oDocument->setUrlPrefix( str_replace($remaining_part, '', $chained_uri) );

            return $com_urls->parse($remaining_part, $chained_uri);

            } else {

                list($com, $view, $method) = explode('.', $sDestination);

                $_GET['_vc'] = $com;
                $_GET['_vv'] = $view;
                $_GET['_vm'] = $method;
                #print_r($_GET);
                return true;
            }

            } else {
                // TODO: Special Message?
                #print "no match".NL;
            }
        }
        // Throw 404
        VResponse::error(404);

        return false;
    }

    static public function _parse($url=null)
    {

        VLoader::import('versions.utilities.array');

        if (!$url) {
            $oInput =& VFactory::getInput();
            $url   = $oInput->get('REQUEST_URI', 'index', 'server');
        }

        $sParsedUrl = parse_url($url);
        $path_parts = VArray::strip_empty_values( explode('/', $sParsedUrl['path']), true );

        $_GET['_vc'] = VArray::get($path_parts, 0);
        $_GET['_vv'] = VArray::get($path_parts, 1);
        $_GET['_vm'] = VArray::get($path_parts, 2);

    }

    public function register($expression, $sDestination=null)
    {
        if (!class_exists('Validator'))
            VLoader::import('versions.utilities.validator');

        if (Validator::is($expression, 'array')) {
            foreach ($expression as $pattern => $dest) {
                $this->register($pattern, $dest);
            }
            return null;
        }

        $this->pattern[$expression] = $sDestination;
        #printf("Expression: %s Destination: %s".NL, $expression, $sDestination);

    }


    function getPattern()
    {
        return $this->pattern;
    }

}