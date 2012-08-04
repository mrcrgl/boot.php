<?php

class ComponentAuthViewLogin extends VApplicationView {
  
  
  public function show() {
    
  	$document =& VFactory::getDocument();
  	$renderer =& $document->getRenderer();
  	
  	$document->setTemplate('login.htpl');
  	
  	$input =& VFactory::getInput();
  	#print $input->get('name', 'nix da', 'get');
  }
  
  public function verify() {
  	
  	$document =& VFactory::getDocument();
  	$input =& VFactory::getInput();
  	
  	if ($input->get('do_login', false, 'post')) {
      $refLogin = new Login("User");
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
      } else {
        $document->assign('errors', $refLogin->getErrorMsgs());
        $document->assign('username', $input->get('username', null, 'post'));
        
      }
    }
    
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