<?php

namespace Andou\Automatedpagetest;

class Cleaner {

  /**
   *
   * @var Andou\Automatedpagetest\App 
   */
  protected $_app;

  /**
   * Returns an instance of this class
   * 
   * @return \Andou\Automatedpagetest\Cleaner
   */
  public static function getInstance($app) {
    $classname = __CLASS__;
    return new $classname($app);
  }

  public function __construct($app) {
    $this->_app = $app;
  }

  public function cleanFolder($folder) {
    $files = scandir($folder);
    foreach ($files as $file) {
      if (preg_match("/^(.+).json$/", $file)) {
        $filepath = $folder . $file;
        unlink($filepath);
      }
    }
    return $this;
  }

}