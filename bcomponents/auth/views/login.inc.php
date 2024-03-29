<?php

class ComponentAuthViewLogin extends BApplicationView
{
  
  
  public function show()
 {
    
      $oDocument =& BFactory::getDocument();
      $oRenderer =& $oDocument->getRenderer();
      $oSession =& BFactory::getSession();
      $oInput =& BFactory::getInput();
      
      $login =& $oSession->get('login');
      if (is_object($login) && $login->loggedIn()) {
          header( sprintf("Location: %s", $oInput->get('referer', '/', 'get')) );
          exit;
      }
      
      $oDocument->setTemplate('login.htpl');
      
      
      #print $oInput->get('name', 'nix da', 'get');
      
      print $oInput->get('referer');
      
      if (strtolower($oInput->getMethod()) == 'post') {
          $this->verify();
      }
      
  }
  
  public function verify()
      {
      
      $oDocument =& BFactory::getDocument();
      $oInput         =& BFactory::getInput();
      $oSession     =& BFactory::getSession();
      
      if ($oInput->get('do_login', false, 'post')) {
      $refLogin = new ComponentAuthModelLogin("User");
      $requested_view = $oInput->get('requested_view', false, 'post');
      if ( $requested_view ) {
        $refLogin->followUrl( $requested_view );
      }
      /*
       * Disabled for development @MR
       **/
      #$refLogin->needPermission('ui.login');
      
      $refLogin->doLogin( $oInput->get('username', null, 'post'), $oInput->get('password', null, 'post'));
      if ($refLogin->loggedIn()) {
        $oDocument->assign('login', true);
        $oSession->set('login', &$refLogin);
      } else {
        #$oDocument->assign('errors', $refLogin->getErrorMsgs());
        $oDocument->assign('username', $oInput->get('username', null, 'post'));
        
      }
    }
    
    header('Location: '.$oInput->get('HTTP_REFERER', '/', 'server'));
    exit;
  }
  
  public function logout()
  {
      $oDocument =& BFactory::getDocument();
      $oSession     =& BFactory::getSession();
      
      #$oDocument->setTemplate('login.htpl');
      
      $login =& $oSession->get('login');
      if (is_object($login)) {
          $login->doLogout();
      }
      
      
      BMessages::_('Ok', 'Logout erfolgreich!', 'success');
      
      header( sprintf('Location: /%s', $oDocument->getUrlPrefix()) );
      exit;
  }
  /*
  public function show()
  {
      
      
      
      $this->registerStyleSheet('jquery.dataTable');
      
      
      $this->registerJavaScript('jquery.dataTables');
      $this->registerJavaScript('bootstrap.datatables');
      $this->registerJavaScript('bootstrap.datatables.paging');
      $this->registerJavaScript('jquery.validate');
      $this->registerJavaScript('do.validate');
      
    
    $this->setTemplate('Login/login');
    
    parent::show();
  }*/
}
?>