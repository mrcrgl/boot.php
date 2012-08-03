<?php

/**
 * The Object Manipulation controller.
 *
 * @copyright 2008 / riegel.it
 * @author Marc Riegel <mr@riegel.it>
 * @version $Revision: 1.8 $
 */
abstract class objectManipulationController extends publicController {
  
  public $requestType = false;
  public $objectName;
  public $objectManagerName;
  public $usePagination = false;
  
  
  public $refObject;
  public $refManager;
  public $refPagination;
  
  private $templateShow;
  private $templateEdit;
  private $templateDetail;
    
  private $isUpdate = false;
  
  public function __contruct() {
    return parent::__construct();
  }
  
  public function prepare() {
    
    if (!$this->requestType) {
      if (strtolower($this->getArg(0)) == "d") {
        $this->requestType = 'Detail';
      } elseif (strtolower($this->getArg(0)) == "new") {
        $this->requestType = 'Edit';
      } elseif (Validator::is($this->getArg(0), 'uid')) {
        $this->requestType = 'Edit';
      } else {
        $this->requestType = 'Show';
      }
    }
    
    
    
    if ($this->requestType == 'Show') {
      if (!$this->refManager) {
        $this->refManager = new $this->objectManagerName();
      }
      
      /*
       * Prepare SubUser
       */
      if (isset($_SESSION[($this->objectManagerName)]['display_subuser'])) {
        $display_subuser = $_SESSION[($this->objectManagerName)]['display_subuser'];
        $this->refManager->display_subuser = $display_subuser;
        
        Instance::f('smarty')->assign('display_subuser', $display_subuser);
      }
      
      if (isset($_SESSION[($this->objectManagerName)]['customer_filter'])) {
        $customer_filter = $_SESSION[($this->objectManagerName)]['customer_filter'];
        $this->refManager->customer_filter = $customer_filter;
        
        Instance::f('smarty')->assign('customer_filter', $customer_filter);
      }
      
      /*
       * Prepare Search
       */
      if (isset($_SESSION[($this->objectManagerName)]['search_request'])) {
        $search_request = $_SESSION[($this->objectManagerName)]['search_request'];
        $this->refManager->clearKeywords();
        foreach (split(" ", $search_request) as $keyword) {
          $this->refManager->setKeyword($keyword);
        }
      
        Instance::f('smarty')->assign('search_request', $search_request);
      }
      
      /*
       * Prepare Pagination
       */
      if ($this->usePagination) {
        $this->refPagination = new Pagination();
        $this->refPagination->setResultCount($this->refManager->getNumRows());
        $this->refPagination->set('page', $this->getArg('page'));
        $this->refPagination->setRequestUri($this->ctrl->_requestPage);
        $this->refManager->pagination = &$this->refPagination;
      }
      
    }
    
    if ($this->requestType == 'Edit') {
      if (!$this->refManager) {
        $this->refManager = new $this->objectManagerName();
      }
      
      /*
       * Prepare SubUser
       */
      if (isset($_SESSION[($this->objectManagerName)]['display_subuser'])) {
        $display_subuser = $_SESSION[($this->objectManagerName)]['display_subuser'];
        $this->refManager->display_subuser = $display_subuser;
        
        Instance::f('smarty')->assign('display_subuser', $display_subuser);
      }
      
      $uid = (is_numeric($this->getArg(0))) ? $this->getArg(0) : null;
      if (is_null($uid) && Validator::is($this->getArg(0), 'hexuid')) {
        $uid = $this->getArg(0);
      }
      try {
        $this->refObject = new $this->objectName($uid);
      } catch (Exception $e) {
        $this->unsetArg('d');
        unset($this->refObject);
        $this->requestType = 'Show';
        return 'prepare';
      }
      if ($this->refObject->isValid()) {
        $this->isUpdate = true;
        Instance::f('smarty')->assign('delete_url', $_SERVER['REQUEST_URI'].'/'.md5($this->refObject->getUID()));
      } else {
        $this->refObject = new $this->objectName();
      }
    }
    
    if ($this->requestType == 'Detail') {
      $uid = (is_numeric($this->getArg(1))) ? $this->getArg(1) : null;
      if (is_null($uid) && Validator::is($this->getArg(1), 'hexuid')) {
        $uid = $this->getArg(1);
      }
      try {
        $this->refObject = new $this->objectName($uid);
      } catch (Exception $e) {
        $this->unsetArg('d');
        unset($this->refObject);
        $this->requestType = 'Show';
        return 'prepare';
      }
    }
    
    return parent::prepare();
  }
  
  public function proceed() {
    
    if ($this->requestType == "Edit" && isset($this->ctrl->_POST['d1']) && $this->ctrl->_POST['d1'] == '1') {
      $bOk = $this->refObject->update($this->ctrl->_POST);
      
      if ($bOk && $this->isUpdate) {
        Instance::f('smarty')->assign('update_success', true);
        $this->message(Text::_('UPDATE_SUCCESSFUL_HEADLINE'), Text::_('UPDATE_SUCCESSFUL_MSG'), 'success');
      } elseif ($bOk) {
        Instance::f('smarty')->assign('insert_success', true);
        $this->message(Text::_('INSERT_SUCCESSFUL_HEADLINE'), Text::_('INSERT_SUCCESSFUL_MSG'), 'success');
      } else {
      	if ($this->isUpdate) {
      		$this->message(Text::_('UPDATE_FAILED_HEADLINE'), Text::_('UPDATE_FAILED_MSG'), 'error');
      	} else {
      		$this->message(Text::_('INSERT_FAILED_HEADLINE'), Text::_('INSERT_FAILED_MSG'), 'error');
      	}
      }
      
      if ($bOk) {
        //$this->ctrl->_args[0] = $this->refObject->getUID();
        $url = str_replace('new', $this->refObject->getUID(), $_SERVER['REQUEST_URI']);
      	$this->redirect($url);
        exit;
      }
      
      unset($this->ctrl->_POST['d1']);
      return 'prepare';
    }
    
    if (isset($this->ctrl->_POST['s1']) && $this->ctrl->_POST['s1'] == '1') {
      
      $this->ctrl->_POST['s'] = trim($this->ctrl->_POST['s']);
      $_SESSION[($this->objectManagerName)]['search_request'] = $this->ctrl->_POST['s'];
      
      unset ($this->ctrl->_POST['s1']);
      return 'prepare';
    }
    
    if (isset($this->ctrl->_POST['u1']) && $this->ctrl->_POST['u1'] == '1') {
      
      $this->ctrl->_POST['u'] = trim($this->ctrl->_POST['u']);
      $_SESSION[($this->objectManagerName)]['display_subuser'] = (isset($this->ctrl->_POST['u']) && $this->ctrl->_POST['u'] == "1") ? true : false;
      
      if (isset($this->ctrl->_POST['u']) && $this->ctrl->_POST['u'] != "1") {
        $_SESSION[($this->objectManagerName)]['customer_filter'] = false;
      }
      
      unset ($this->ctrl->_POST['u1']);
      return 'prepare';
    }
    
    if (isset($this->ctrl->_POST['c1']) && $this->ctrl->_POST['c1'] == '1') {
      
      $this->ctrl->_POST['c'] = trim($this->ctrl->_POST['c']);
      $_SESSION[($this->objectManagerName)]['customer_filter'] = (strlen($this->ctrl->_POST['c']) == 13) ? $this->ctrl->_POST['c'] : false;
      
      unset ($this->ctrl->_POST['c1']);
      return 'prepare';
    }
    
    if ($this->requestType == "Edit" && strtolower($this->getArg(1)) == md5($this->refObject->getUID())) {
      $bOk = $this->refObject->delete();
      if ($bOk) {
        //Instance::f('smarty')->assign('delete_success', true);
        $this->message(Text::_('DELETE_SUCCESSFUL_HEADLINE'), Text::_('DELETE_SUCCESSFUL_MSG'), 'success');
        
        $url = preg_replace('/\/([0-9a-f]{13})\/([0-9a-f]{32})$/', '', $_SERVER['REQUEST_URI']);
      	$this->redirect($url);
        exit;
      } else {
        Instance::f('smarty')->assign('error', $this->refObject->getErrorMsgs());
        $this->message(Text::_('DELETE_FAILED_HEADLINE'), Text::_('DELETE_FAILED_MSG'), 'error');
      }
      
    }
    
    if (is_object($this->refObject) && $this->refObject->isValid() && strtolower($this->getArg('cs')) == md5($this->refObject->getUID())) {
      $bOk = $this->refObject->delete();
      if ($bOk) {
        //Instance::f('smarty')->assign('delete_success', true);
        $this->message(Text::_('DELETE_SUCCESSFUL_HEADLINE'), Text::_('DELETE_SUCCESSFUL_MSG'), 'success');
        
        $this->unsetArg('d');
        $this->unsetArg('cs');
        unset($this->refObject);
        $this->requestType = 'Show';
        return 'prepare';
      } else {
        $this->message(Text::_('DELETE_FAILED_HEADLINE'), Text::_('DELETE_FAILED_MSG'), 'error');
        $this->unsetArg('d');
        $this->unsetArg('cs');
        $this->requestType = 'Show';
        return 'prepare';
      }
      
    }
    
    return parent::proceed();
  }
  
  public function show() {
    
    Instance::f('smarty')->assign('isUpdate', $this->isUpdate);
    Instance::f('smarty')->assign('pagination', $this->refPagination);
    Instance::f('smarty')->assign($this->objectName, $this->refObject);
    Instance::f('smarty')->assign($this->objectManagerName, $this->refManager);
    
    if ($this->requestType == 'Show') {
      $this->setTemplate($this->templateShow);
    } elseif ($this->requestType == 'Detail') {
      $this->setTemplate($this->templateDetail);
    } else {
      $this->setTemplate($this->templateEdit);
    }
    
    return parent::show();
  }
  
  protected function setObject($__objectName) {
    $this->objectName = $__objectName;
  }
  
  protected function setManager($__objectName) {
    $this->objectManagerName = $__objectName;
  }
  
  protected function setShowTemplate($__templateShow) {
    $this->templateShow = $__templateShow;
  }
  
  protected function setEditTemplate($__templateEdit) {
    $this->templateEdit = $__templateEdit;
  }
  
  protected function setDetailTemplate($__templateDetail) {
    $this->templateDetail = $__templateDetail;
  }
}

?>