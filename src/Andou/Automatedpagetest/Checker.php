<?php

namespace Andou\Automatedpagetest;

use Andou\Webpagetestwrapper\Webpagetestwrapper;

class Checker {

  /**
   *
   * @var Andou\Automatedpagetest\App 
   */
  protected $_app;

  /**
   * 
   * @var Andou\Webpagetestwrapper\Webpagetestwrapper 
   */
  protected $_api;

  /**
   *
   * @var int 
   */
  protected $_sleep_time;

  /**
   *
   * @var int
   */
  protected $_max_rounds;

  /**
   * Returns an instance of this class
   * 
   * @return \Andou\Automatedpagetest\Checker
   */
  public static function getInstance($app) {
    $classname = __CLASS__;
    return new $classname($app);
  }

  public function __construct($app) {
    $this->_app = $app;
    $this->_sleep_time = $this->_app->getConfigs()->getWebpagetestPollingTime();
    $this->_api = Webpagetestwrapper::getInstance($this->_app->getConfigs()->getWebpagetestApiKey());
    $this->_max_rounds = $this->_app->getConfigs()->getWebpagetestMaxRounds();
  }

  public function fetchStatuses($fetching_folder, $results_folder, $executed_scheduled_folder) {
    $round = 0;

    $this->_app->_echo("Checking statuses");

    while ($this->_fetchStatuses($fetching_folder, $results_folder, $round, $executed_scheduled_folder) && $round < $this->_max_rounds) {
      sleep($this->_sleep_time);
      $round++;
    }
    if ($round == 0) {
      $this->_app->_echo("Nothing to check.. :(");
    } else {
      $this->_app->_echo("All tests done!");
      $this->_app->_echo("");
    }
  }

  protected function _fetchStatuses($fetching_folder, $results_folder, $round, $executed_scheduled_folder) {
    $found = 0;

    $this->_app->_echo("Checking statuses - round $round");
    $files = scandir($fetching_folder);
    foreach ($files as $file) {
      if (preg_match("/^test(.+).json$/", $file)) {
     
        $found++;
        $filepath = $fetching_folder . $file;
        $test_sched = json_decode(file_get_contents($filepath), TRUE);
        $test_id = $test_sched['data']['testId'];
        $this->_app->_echo("Checking $file [$test_id]...");
        $status = json_decode($this->_api->testStatus($test_id), TRUE);
        if ($status['data']['statusCode'] === 200) {
          copy($filepath, $executed_scheduled_folder . date("Y_m_d_H_i_s") . '_' . $file);
          unlink($filepath);
          $found--;
          $test_result = $this->_api->testResults($test_id);
          file_put_contents($results_folder . $file, $test_result);
          $this->_app->_echo("[TEST COMPLETED] $file test is completed!");
        } else {
          $this->_app->_echo("[TEST PENDING] $file test is not completed");
          //$this->_app->_echo(print_r($status, TRUE));
        }
      }
    }
    if ($found != 0) {
      $this->_app->_echo("Some tests aren't completed");
    }
    $this->_app->_echo("");
    return $found != 0;
  }

}