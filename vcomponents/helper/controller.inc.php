<?php



class ComponentHelperController extends VApplicationControllerWeb 
{
    
    var $default_view = 'index';
    
public function __construct()
    {
      
        $document =& VFactory::getDocument();
      $document->setTitle('Versions 2.0 - Helper');
      $document->setProjectName('Versions 2.0 - Helper');
      
      /*
      $session     =& VFactory::getSession();
      $input         =& VFactory::getInput();
      $login         =& $session->get('login');
      
      if (!is_object($login) || !$login->loggedIn()) {
          header( sprintf("Location: /%slogin", $document->getUrlPrefix()) );
          exit;
      }*/
      
      parent::__construct();
  }
    
}