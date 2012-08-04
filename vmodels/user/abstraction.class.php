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

abstract class UserAbstraction extends VModelConnector {
  
  public function changePassword($param=false, $sendMail=false) {
    if ($param === false) {
      $strNewPass = VPassword::create(8);
      Instance::f('smarty')->assign('created_password', $strNewPass);
    } elseif (is_array($param) && isset($param['password']) && isset($param['password_retype'])) {
      
      if (strlen($param['password']) < 5) {
        $this->setErrorMsg("passwordToShort");
        return false;
      }
      if ($param['password'] != $param['password_retype']) {
        $this->setErrorMsg("passwordRetypeNotEqual");
        return false;
      }
      $strNewPass = $param['password'];
    } else {
      $this->setErrorMsg("changingPasswordMissingParam");
      return false;
    }
    
    if (!$this->update($param, true)) {
      $this->setErrorMsg("errorCommon");
      
      return false;
    }
    
    if ($sendMail === true) {
      Instance::f('smarty')->assign('User', $this);
      $bOk = $this->sendNewPasswordByMail();
      if (!$bOk) return false;
    }
    
    return true;
  }
  
  private function sendNewPasswordByMail() {
    $refMailer = new PHPMailer();
    $refMailer->AddAddress($this->email, $this->fullname);
    $refMailer->Subject = "[clickhotel.tv] Ihr neues Passwort";
    $refMailer->Body    = Instance::f('smarty')->fetch('/Customer/User/Mail/resend-password.htpl');
    if (!$refMailer->Send()) {
      $this->setErrorMsg("errorSendingMailFailed");
    } else {
      return true;
    }
    return false;
  }
}
?>