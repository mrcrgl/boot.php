<?php

VLoader::register('PHPMailer', VLIB.DS.'PHPMailer'.DS.'class.phpmailer.php');

class VMailer extends PHPMailer {

  static $_instance = null;

  static function &getInstance() {

    if (!self::$_instance) {
      self::$_instance = new VMailer();
    }

    return self::$_instance;
  }

  public function __construct() {


    $smtpauth = VSettings::f('mailer.smtphost', 0);
    $smtpauth = VSettings::f('mailer.smtpsecure', 0);

    $this->SetFrom(VSettings::f('mailer.sendermail', 'root@localhost'), VSettings::f('mailer.sendername', 'root'));

    $method = VSettings::f('mailer.method', 'phpmail');
    /*
     * Set to smtp
     */
    if ($method == 'smtp') {

      $this->IsSmtp();

      $this->Host = VSettings::f('mailer.smtphost', 'localhost'); // sets the SMTP server
      $this->Port = VSettings::f('mailer.smtpport', 25); // set the SMTP port for the GMAIL server


      if (VSettings::f('mailer.smtpauth', null)) {
        $user = VSettings::f('mailer.smtpuser', null);
        $pass = VSettings::f('mailer.smtppass', null);

        if (!$user || !$pass) {
          throw new Exception("mailer.smtpauth requires smtpuser and smtppass to be set.");
        }

        $this->SMTPAuth   = true;                  // enable SMTP authentication
        $this->Username   = $user; // SMTP account username
        $this->Password   = $pass;        // SMTP account password


      }
      $smtpauth = VSettings::f('mailer.smtpuser', 0);
      $smtpauth = VSettings::f('mailer.smtppass', 0);

    }
    /*
     * set to sendmail
     */
    elseif ($method == 'sendmail') {

      $this->IsSendmail();

    }
    /*
     * fallback to php's mail function
     */
    else {

      // nothing to do

    }

    $this->AltBody    = "To view the message, please use an HTML compatible email viewer!";

    parent::__construct();
  }

  public function setDebug($num=1) {
    $this->SMTPDebug  = $num;
  }
}