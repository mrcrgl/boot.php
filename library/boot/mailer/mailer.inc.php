<?php

BLoader::register('PHPMailer', BLIB.DS.'PHPMailer'.DS.'class.phpmailer.php');

class BMailer extends PHPMailer 
{

  static $_instance = null;

  static function &getInstance()
 {

    if (!self::$_instance) {
      self::$_instance = new BMailer();
    }

    return self::$_instance;
  }

  public function __construct()
  {


    $smtpauth = BSettings::f('mailer.smtphost', 0);
    $smtpauth = BSettings::f('mailer.smtpsecure', 0);

    $this->SetFrom(BSettings::f('mailer.sendermail', 'root@localhost'), BSettings::f('mailer.sendername', 'root'));

    $method = BSettings::f('mailer.method', 'phpmail');
    /*
     * Set to smtp
     */
    if ($method == 'smtp') {

      $this->IsSmtp();

      $this->Host = BSettings::f('mailer.smtphost', 'localhost'); // sets the SMTP server
      $this->Port = BSettings::f('mailer.smtpport', 25); // set the SMTP port for the GMAIL server


      if (BSettings::f('mailer.smtpauth', null)) {
        $user = BSettings::f('mailer.smtpuser', null);
        $pass = BSettings::f('mailer.smtppass', null);

        if (!$user || !$pass) {
          throw new Exception("mailer.smtpauth requires smtpuser and smtppass to be set.");
        }

        $this->SMTPAuth   = true;                  // enable SMTP authentication
        $this->Username   = $user; // SMTP account username
        $this->Password   = $pass;        // SMTP account password


      }
      $smtpauth = BSettings::f('mailer.smtpuser', 0);
      $smtpauth = BSettings::f('mailer.smtppass', 0);

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

  public function setDebug($num=1)
  {
    $this->SMTPDebug  = $num;
  }
}