<?php
/**
 * DMT - Developer Moddelling Tool
 * Created on 24.07.2007
 *
 * @author Marc Riegel
 * @version 1.0
 *
 * ---------------------------------
 *
 * ---------------------------------
 *
 */

class ComponentAuthModelLogin extends BObject 
{

  private $tsLogin    = false;

  private $objUser    = false;

  private $loginType  = "";

  private $useReferer = false;

  private $followUrl  = false;

  private $strPermission = false;

  public function __construct($__loginType="User")
 {
    $this->loginType = $__loginType;
  }

  public function __get($__memberName)
  {
    if ($__memberName == 'obj') {
      if (!is_object($this->objUser)) {
        return new User();
      }
      return $this->objUser;
    }
  }

  public function doLogin($username, $password)
    {
    if (!Validator::is($username, 'filled')) {
      #$this->setErrorMsg("loginUserRequired");
      BMessages::_('Error', 'loginUserRequired', 'error');
      return false;
    }
    if (!Validator::is($password, 'filled')) {
      #$this->setErrorMsg("loginPassRequired");
      BMessages::_('Error', 'loginPassRequired', 'error');
      return false;
    }

    $user = new $this->loginType();
    if ($user->getModelVersion() == 1) {
        $managerClass  = $this->loginType."Manager";
        $objManager    = new $managerClass();
        $this->objUser = $objManager->doLogin($username, $password);
    } elseif ($user->getModelVersion() == 2) {
        $user->objects->filter(sprintf('[username:%s,password:%s]', $username, md5($password)))->get();
        $this->objUser = $user;
    }


    if (is_object($this->objUser) && $this->objUser->isValid()) {

      if ($this->strPermission && !$this->objUser->hasPermission($this->strPermission, true)) {
        #$this->setErrorMsg("permissionDenied");
        BMessages::_('Error', 'permissionDenied', 'error');
        unset($this->objUser);
        return false;
      }

      $this->loginSuccessful();
      return true;
    } else {
      #$this->setErrorMsg("invalidUserPasswordCombi");
      BMessages::_('Error', 'invalidUserPasswordCombi', 'error');
      return false;
    }
  }

  public function doLogout()
    {

      $oSession =& BFactory::getSession();
      $oSession->clear('login');

    unset($this->objUser);
    return true;
  }

  public function loggedIn()
  {
    if (!isset($this->objUser)) {
      return false;
    }
    if (!is_object($this->objUser)) {
      return false;
    }
    if (!$this->objUser->isValid()) {
      return false;
    }
    if ($this->objUser->status < 1) {
      return false;
    }

    return true;
  }

  public function setUser($user)
  {
    $this->objUser = $user;
  }

  private function loginSuccessful()
  {
    $this->registerLogin();
    $this->forwarding();
  }

  private function registerLogin()
  {
    $this->tsLogin = time();
    $oSession =& BFactory::getSession();
    $oSession->set('login', &$this);
  }

  private function forwarding()
  {
    $headerLocation = false;
    if ($this->useReferer == true && Validator::is($_SERVER['HTTP_REFERER'], 'filled')) {
      $headerLocation = $_SERVER['HTTP_REFERER'];
    }
    if (Validator::is($this->followUrl, 'filled')) {
      $headerLocation = $this->followUrl;
    }
    if ($headerLocation) {
      header('Location: '.$headerLocation);
      exit;
    }
  }

  public function useReferer($bool=true)
    {
    $this->useReferer = $bool;
  }

  public function followUrl($url=false)
  {
    $this->followUrl = $url;
  }

  public function needPermission($permission=false)
  {
    $this->strPermission = $permission;
  }

}
?>
