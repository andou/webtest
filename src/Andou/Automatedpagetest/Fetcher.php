<?php

namespace Andou\Automatedpagetest;

class Fetcher {

  /**
   *
   * @var Andou\Automatedpagetest\App 
   */
  protected $_app;

  /**
   * Returns an instance of this class
   * 
   * @return \Andou\Automatedpagetest\Fetcher
   */
  public static function getInstance($app) {
    $classname = __CLASS__;
    return new $classname($app);
  }

  public function __construct($app) {
    $this->_app = $app;
  }

  public function fetchTests($fetching_folder) {
    $res = array();
    $this->_app->_echo("Fetching tests");
    $files = scandir($fetching_folder);
    foreach ($files as $file) {
      if (preg_match("/^test(.+).json$/", $file)) {
        $this->_app->_echo("Fetching $file ...");
        $test_definition = file_get_contents($fetching_folder . $file);
        if (!$test_definition) {
          die('Bad JSON definition in ' . $file);
        }
        $res[$file] = json_decode($test_definition, TRUE);
        $this->_app->_echo("$file fetched!");
      }
    }
    $this->_app->_echo("Done fetching");
    $this->_app->_echo("");
    return $res;
  }

}