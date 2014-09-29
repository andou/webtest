<?php

namespace Andou\Automatedpagetest;

use Andou\Webpagetestwrapper\Webpagetestwrapper;

class Scheduler {

  /**
   * 
   * @var \Andou\Webpagetestwrapper\Webpagetestwrapper 
   */
  protected $_api;

  /**
   *
   * @var \Andou\Automatedpagetest\App 
   */
  protected $_app;

  /**
   * Returns an instance of this class
   * 
   * @return \Andou\Automatedpagetest\Scheduler
   */
  public static function getInstance($app) {
    $classname = __CLASS__;
    return new $classname($app);
  }

  public function __construct($app) {
    $this->_app = $app;
    $this->_api = Webpagetestwrapper::getInstance($this->_app->getConfigs()->getWebpagetestApiKey());
  }

  public function scheduleTests($tests, $schedule_folder) {
    $this->_app->_echo("Scheduling tests");
    foreach ($tests as $filename => $test) {
      $_filename = explode(".", $filename);
      $scheds = $this->_scheduleTest($test);
      $cnt = 0;
      foreach ($scheds as $sched_descr => $sched) {
        $schedule_result = json_decode($sched, TRUE);
        if ($schedule_result['statusCode'] === 200) {
          file_put_contents($schedule_folder . $_filename[0] . "_" . $cnt . ".json", $sched);
        } else {
          $this->_app->_echo("[ERROR] [$sched_descr] " . $schedule_result['statusText']);
        }
        $cnt++;
      }
    }
    $this->_app->_echo("Done scheduling");
    $this->_app->_echo("");
  }

  protected function _scheduleTest($test) {
    $res = array();
    $this->_app->_echo("Scheduling " . $test['name']);
    $basepath = $test['basepath'];

    foreach ($test['test_cases'] as $test_case) {
      $browser = isset($test_case['browser']) ? $test_case['browser'] : NULL;
      $location = isset($test_case['location']) ? $test_case['location'] : NULL;
      $connectivity = isset($test_case['connectivity']) ? $test_case['connectivity'] : NULL;
      $runs = isset($test_case['runs']) ? $test_case['runs'] : 1;

      $test_sched = $this->_api
              ->setBrowser($browser)
              ->setLocation($location)
              ->setConnectivity($connectivity)
              ->runTest($basepath . $test_case['url'], $runs);
      $res[$this->_api->composeLocation() . "@" . $test_case['url']] = $test_sched;
    }

    return $res;
  }

}