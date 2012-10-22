<?php



class ComponentHelperController extends BApplicationControllerWeb 
{
    
    var $default_view = 'index';
    
public function __construct()
    {
      
        $oDocument =& BFactory::getDocument();
      $oDocument->setTitle('Versions 2.0 - Helper');
      $oDocument->setProjectName('Versions 2.0 - Helper');
      
      /*
      $session     =& BFactory::getSession();
      $input         =& BFactory::getInput();
      $login         =& $oSession->get('login');
      
      if (!is_object($login) || !$login->loggedIn()) {
          header( sprintf("Location: /%slogin", $oDocument->getUrlPrefix()) );
          exit;
      }*/
      
      parent::__construct();
  }
    
}