<?php

namespace Andou\Automatedpagetest;

class Mailer {

  protected $_mailto;
  protected $_mailfrom;
  protected $_mailnicefrom;
  protected $_mailobj;

  /**
   * Returns an instance of this class
   * 
   * @return \Andou\Automatedpagetest\Mailer
   */
  public static function getInstance($app) {
    $classname = __CLASS__;
    return new $classname($app);
  }

  public function __construct($app) {
    $this->_app = $app;
    $this->_mailto = $this->_app->getConfigs()->getMailMailto();
    $this->_mailfrom = $this->_app->getConfigs()->getMailMailfrom();
    $this->_mailnicefrom = $this->_app->getConfigs()->getMailNicefrom();
    $this->_mailobj = $this->_app->getConfigs()->getMailMailobj();
  }

  public function sendMail($file_path) {
    if (
            isset($this->_mailto) &&
            isset($this->_mailfrom) &&
            isset($this->_mailnicefrom) &&
            isset($this->_mailobj)
    ) {
      $email = new \PHPMailer();
      $email->From = $this->_mailfrom;
      $email->FromName = $this->_mailnicefrom;
      $email->Subject = $this->_replacePlaceholders($this->_mailobj);
      $email->Body = "Reports for today";
      $email->AddAddress($this->_mailto);

      $email->AddAttachment($file_path);

      return $email->Send();
    } else {
      return FALSE;
    }
  }

  /**
   * Replaces a string with placeholder
   * 
   * @param string $string
   * @return string
   */
  protected function _replacePlaceholders($string) {
    $_string = str_replace("%data%", date("d/m/Y"), $string);
    return $_string;
  }

}