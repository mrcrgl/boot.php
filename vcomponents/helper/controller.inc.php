<?php



class ComponentHelperController extends VApplicationControllerWeb 
{
    
    var $default_view = 'index';
    
public function __construct()
    {
      
        $oDocument =& VFactory::getDocument();
      $oDocument->setTitle('Versions 2.0 - Helper');
      $oDocument->setProjectName('Versions 2.0 - Helper');
      
      /*
      $session     =& VFactory::getSession();
      $input         =& VFactory::getInput();
      $login         =& $oSession->get('login');
      
      if (!is_object($login) || !$login->loggedIn()) {
          header( sprintf("Location: /%slogin", $oDocument->getUrlPrefix()) );
          exit;
      }*/
      
      parent::__construct();
  }
    
}