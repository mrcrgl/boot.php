<?php

class ComponentAuthViewLogin extends VApplicationView {
  
  
  public function show() {
    
  	$document =& VFactory::getDocument();
  	$renderer =& $document->getRenderer();
  	$session =& VFactory::getSession();
  	$input =& VFactory::getInput();
  	
  	$login =& $session->get('login');
  	if (is_object($login) && $login->loggedIn()) {
  		header( sprintf("Location: %s", $input->get('referer', '/', 'get')) );
  		exit;
  	}
  	
  	$document->setTemplate('login.htpl');
  	
  	
  	#print $input->get('name', 'nix da', 'get');
  	
  	print $input->get('referer');
  	
  	if (strtolower($input->getMethod()) == 'post') {
  		$this->verify();
  	}
  	
  }
  
  public function verify() {
  	
  	$document =& VFactory::getDocument();
  	$input 		=& VFactory::getInput();
  	$session 	=& VFactory::getSession();
  	
  	if ($input->get('do_login', false, 'post')) {
      $refLogin = new ComponentAuthModelLogin("User");
      $requested_view = $input->get('requested_view', false, 'post');
      if ( $requested_view ) {
        $refLogin->followUrl( $requested_view );
      }
      /*
       * Disabled for development @MR
       **/
      #$refLogin->needPermission('ui.login');
      
      $refLogin->doLogin( $input->get('username', null, 'post'), $input->get('password', null, 'post'));
      if ($refLogin->loggedIn()) {
        $document->assign('login', true);
        $session->set('login', &$refLogin);
      } else {
        #$document->assign('errors', $refLogin->getErrorMsgs());
        $document->assign('username', $input->get('username', null, 'post'));
        
      }
    }
    
    header('Location: '.$input->get('HTTP_REFERER', '/', 'server'));
    exit;
  }
  
  public function logout() {
  	$document =& VFactory::getDocument();
  	$input 		=& VFactory::getInput();
  	$session 	=& VFactory::getSession();
  	
  	#$document->setTemplate('login.htpl');
  	
  	$login =& $session->get('login');
  	if (is_object($login)) {
  		$login->doLogout();
  	}
  	
  	
  	VMessages::_('Ok', 'Logout erfolgreich!', 'success');
  	
  	header( sprintf('Location: /%s', $document->getUrlPrefix()) );
  	exit;
  }
  /*
  public function show() {
  	
  	
  	
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