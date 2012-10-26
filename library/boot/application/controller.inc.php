<?php
/**
 * @author Marc Riegel
 */

if (!class_exists('BObject'))
    BLoader::import('boot.base.object');

/**
 * The main Application Controller
 *
 * @author marc
 *
 */
class BApplicationController extends BObject implements BApplicationControllerInterface
{

  /**
   *
   * @var unknown_type
   */
    static private $_aComponents = null;

    /**
     *
     * @var unknown_type
     */
    var $sDefaultView             = null;
    
    var $oComponent = null;

    /**
     *
     * @var unknown_type
     */
    var $sComponentRoot         = null;

    /**
     *
     * @var unknown_type
     */
    var $sComponentName         = null;

    /**
     *
     */
    var $sComponentSettings = null;

    /**
     *
     * @var unknown_type
     */
    var $sRequestViewClassname = null;

    /**
     *
     * @var unknown_type
     */
    var $sRequestViewMethod = null;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {

    }

    static public function getInstance($sComponent=null)
    {

        if (!$sComponent) {
            $oInput =& BFactory::getInput();
            $sComponent = $oInput->get('_vc', 'default', 'get');
        }

        $sControllerClassname = self::getControllerByPrefix($sComponent);

        if (!Validator::is($sControllerClassname, 'filled')) {
            $sMessage = sprintf(
                "Controller for component '%s' not " .
                "found or component does not exist!",
                $sComponent
            );
            throw new Exception($sMessage);
        }

        if (!class_exists($sControllerClassname))
            BLoader::autoload($sControllerClassname);

        if (!class_exists($sControllerClassname))
{
            $sMessage = sprintf(
                "Controller '%s' not found!",
                $sControllerClassname
            );
            throw new Exception($sMessage);
        }

        if (!class_exists('BArray'))
{
          BLoader::import('boot.utilities.array');
        }

        $oCurrentController = new $sControllerClassname();

        $oCurrentController->set(
            'sComponentRoot',
            dirname(BArray::get(BLoader::$registred, $sControllerClassname))
        );

        $oCurrentController->set('sComponentName', $sComponent);

        $oConfig = new BSettingsIni();
        $oConfig->init(
            $oCurrentController->get('sComponentRoot'),
            'controller'
        );

        $oCurrentController->set('sComponentSettings', $oConfig);

        return $oCurrentController;
    }

    static public function getControllerByPrefix($sPrefix)
    {
        if (!self::$_aComponents) {
            self::scanComponents();
        }

        BLoader::import('boot.utilities.array');

        $sPath = BArray::get(self::$_aComponents, $sPrefix);

        return $sPath;
    }

    static public function scanComponents()
    {
        self::$_aComponents = array();

        foreach (array(PROJECT_COMPONENTS, VCOMPONENTS) as $sPath) {

            if (is_dir($sPath)) {

                foreach (scandir($sPath) as $sComponentDir) {
                    if ($sComponentDir == '.' || $sComponentDir == '..')
                        continue;

                    if (!is_dir($sPath.DS.$sComponentDir))
                        continue;

                    if (!is_file($sPath.DS.$sComponentDir.DS.'controller.ini'))
                        continue;

                    if (!is_file($sPath.DS.$sComponentDir.DS.'urls.inc.php'))
                        continue;


                    $oConfig = new BSettingsIni();
                    $oConfig->init(
                        $sPath.DS.$sComponentDir,
                        'controller'
                    );

                    $sAlias = $oConfig->get(
                        'controller.alias',
                        $sComponentDir
                    );

                    // TODO: unused?
                    /*
                    $file     = $sPath.DS
                              . $sComponentDir.DS
                              . $oConfig->get(
                                  'controller.file',
                                  'controller.inc.php'
                                );
                    */
                    $sClassname = sprintf(
                        'Component%sController',
                        ucfirst($sComponentDir)
                    );

                    self::$_aComponents[$sAlias] = $sClassname;
                }

            }
        }
    }
    
    public function register()
    {
        // Register this controller in the paths.
        print "Register controller: ".get_class($this).NL;
    }

    public function handleRequest()
    {

        BMiddleware::trigger('onBeforePrepareRequest');
        $this->prepareRequest();
        BMiddleware::trigger('onAfterPrepareRequest');

        BMiddleware::trigger('onBeforeProcessRequest');
        $this->processRequest();
        BMiddleware::trigger('onAfterProcessRequest');

        /* switch to response */
        BMiddleware::trigger('onBeforePrepareResponse');
        $this->prepareResponse();
        BMiddleware::trigger('onAfterPrepareResponse');

        BMiddleware::trigger('onBeforePrintResponse');
        $this->printResponse();
        BMiddleware::trigger('onAfterPrintResponse');

        BMiddleware::trigger('onBeforeQuit');
        $this->quit();

    }

    public function prepareRequest()
    {
        $sViewIdent = $this->getRequestView();
        $oDocument =& BFactory::getDocument();
        #$oRenderer =& $oDocument->getRenderer();
        #$oRenderer->init();

        $sFilename = $this->oComponent->getViewPath().DS.$sViewIdent;

        // Import view file
        if (!BLoader::check_extensions($sFilename)) {
            // Throw 404
            BResponse::error(404, "View not found.");
        }


        $sViewClassname = $this->oComponent->getViewClassname($sViewIdent);

        $sMethod = $this->getRequestMethod();

        $this->set('sRequestViewClassname', $sViewClassname);
        $this->set('sRequestViewMethod', $sMethod);

    }

    public function processRequest()
    {

        $sViewClassname = $this->get('sRequestViewClassname');
        $sMethod        = $this->get('sRequestViewMethod');


        $oView = new $sViewClassname();

        if (!method_exists($oView, $sMethod)) {
            $sMessage = sprintf(
                "Method '%s' not registred in view '%s'",
                $sMethod,
                $sViewClassname
            );
            throw new Exception($sMessage);
        }

        // prepare
        BMiddleware::trigger('onBeforePrepareView');
        $oView->prepare();
        BMiddleware::trigger('onAfterPrepareView');

        // process method
        BMiddleware::trigger('onBeforeProcessView');
        $oView->$sMethod();
        BMiddleware::trigger('onAfterProcessView');

        // cleanup
        BMiddleware::trigger('onBeforeCleanupView');
        $oView->cleanup();
        BMiddleware::trigger('onAfterCleanupView');


    }

    public function prepareResponse()
    {

        $oDocument =& BFactory::getDocument();
        $oDocument->render();

        BResponse::setBody($oDocument->getBody());

    }

    public function printResponse()
    {

        print BResponse::toString(true);

    }

    public function getRequestView()
    {

        $oInput =& BFactory::getInput();
        $sViewIdent = $oInput->get('_vv', $this->sDefaultView, 'get');

        // Throw 404
        if (!$sViewIdent) BResponse::error(404);

        return $sViewIdent;
    }

    public function getRequestMethod()
    {

        $oInput =& BFactory::getInput();
        $sMethod = $oInput->get('_vm', 'show', 'get');

        // Throw 404
        if (!$sMethod) BResponse::error(404);

        return $sMethod;
    }

    public function getViewClassname($sViewIdent)
    {
        $sClassname = sprintf(
            'Component%sView%s',
            ucfirst($this->get('sComponentName')),
            ucfirst($sViewIdent)
        );

        return $sClassname;
    }

    public function quit()
    {
        exit(0);
    }
}
