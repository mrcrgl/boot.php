<?php
/**
 * @author Marc Riegel
 */

VLoader::import('versions.base.object');

/**
 * The main Application Controller
 *
 * @author marc
 *
 */
class VApplicationController extends VObject
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
            $oInput =& VFactory::getInput();
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
            VLoader::autoload($sControllerClassname);

        if (!class_exists($sControllerClassname)) {
            $sMessage = sprintf(
                "Controller '%s' not found!",
                $sControllerClassname
            );
            throw new Exception($sMessage);
        }

        if (!class_exists('VArray')) {
          VLoader::import('versions.utilities.array');
        }

        $oCurrentController = new $sControllerClassname();

        $oCurrentController->set(
            'sComponentRoot',
            dirname(VArray::get(VLoader::$registred, $sControllerClassname))
        );

        $oCurrentController->set('sComponentName', $sComponent);

        $oConfig = new VSettingsIni();
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

        VLoader::import('versions.utilities.array');

        $sPath = VArray::get(self::$_aComponents, $sPrefix);

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


                    $oConfig = new VSettingsIni();
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

    public function handleRequest()
    {

        VMiddleware::trigger('onBeforePrepareRequest');
        $this->prepareRequest();
        VMiddleware::trigger('onAfterPrepareRequest');

        VMiddleware::trigger('onBeforeProcessRequest');
        $this->processRequest();
        VMiddleware::trigger('onAfterProcessRequest');

        /* switch to response */
        VMiddleware::trigger('onBeforePrepareResponse');
        $this->prepareResponse();
        VMiddleware::trigger('onAfterPrepareResponse');

        VMiddleware::trigger('onBeforePrintResponse');
        $this->printResponse();
        VMiddleware::trigger('onAfterPrintResponse');

        VMiddleware::trigger('onBeforeQuit');
        $this->quit();

    }

    public function prepareRequest()
    {
        $sViewIdent = $this->getRequestView();
        $oDocument =& VFactory::getDocument();
        #$renderer =& $oDocument->getRenderer();
        #$renderer->init();

        $sFilename = $this->sComponentRoot.DS.'views'.DS.$sViewIdent;

        // Import view file
        if (!VLoader::check_extensions($sFilename)) {
            // Throw 404
          VResponse::error(404);
        }


        $sViewClassname = $this->getViewClassname($sViewIdent);

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
        VMiddleware::trigger('onBeforePrepareView');
        $oView->prepare();
        VMiddleware::trigger('onAfterPrepareView');

        // process method
        VMiddleware::trigger('onBeforeProcessView');
        $oView->$sMethod();
        VMiddleware::trigger('onAfterProcessView');

        // cleanup
        VMiddleware::trigger('onBeforeCleanupView');
        $oView->cleanup();
        VMiddleware::trigger('onAfterCleanupView');


    }

    public function prepareResponse()
    {

        $oDocument =& VFactory::getDocument();
        $oDocument->render();

        VResponse::setBody($oDocument->getBody());

    }

    public function printResponse()
    {

        print VResponse::toString(true);

    }

    public function getRequestView()
    {

        $oInput =& VFactory::getInput();
        $sViewIdent = $oInput->get('_vv', $this->sDefaultView, 'get');

        // Throw 404
        if (!$sViewIdent) VResponse::error(404);

        return $sViewIdent;
    }

    public function getRequestMethod()
    {

        $oInput =& VFactory::getInput();
        $sMethod = $oInput->get('_vm', 'show', 'get');

        // Throw 404
        if (!$sMethod) VResponse::error(404);

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
