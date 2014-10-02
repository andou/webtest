<?php

namespace Andou\Automatedpagetest;

use Andou\Webpagetestwrapper\Webpagetestwrapper;
use Andou\Automatedpagetest\Scheduler;
use Andou\Automatedpagetest\Fetcher;
use Andou\Automatedpagetest\Checker;
use Andou\Inireader\Inireader;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class App {

  /**
   * Folder with tests to run
   *
   * @var string
   */
  protected $_tests_folder;

  /**
   * Folder with scheduled tests
   *
   * @var string
   */
  protected $_scheduled_folder;

  /**
   * Folder with test results
   *
   * @var string
   */
  protected $_results_folder;

  /**
   * Folder with test reports
   *
   * @var string
   */
  protected $_report_folder;

  /**
   * Should we be?
   *
   * @var boolean 
   */
  protected $_verbose;

  /**
   * If we need to fetch for tests
   *
   * @var boolean 
   */
  protected $_fetch_tests;

  /**
   * If we need to retrieve results
   *
   * @var boolean 
   */
  protected $_retrieve_results;

  /**
   * If we need to report results
   *
   * @var boolean 
   */
  protected $_report_results;

  /**
   *
   * @var \Andou\Inireader\Inireader
   */
  protected $_configs;

  /**
   * 
   * @var Andou\Webpagetestwrapper\Webpagetestwrapper 
   */
  protected $_api;

  /**
   *
   * @var \Andou\Shelltools\Outputprovider 
   */
  protected $_output_provider = NULL;

  /**
   * Are we?
   *
   * @var boolean 
   */
  protected $_initialized = FALSE;

  /**
   * Returns an instance of this class
   * 
   * @return \Andou\Automatedpagetest\App
   */
  public static function getInstance() {
    $classname = __CLASS__;
    return new $classname();
  }

  /**
   * Class constructor
   *  
   */
  public function __construct() {
    
  }

  /**
   * Initialize application
   * 
   * @param string $inifile
   * @param boolean $fetch_tests
   * @param boolean $verbose
   * @return \Andou\Automatedpagetest\App
   */
  public function init($inifile, $fetch_tests = TRUE, $retrieve_results = TRUE, $report_results = TRUE, $verbose = TRUE) {
    $this->_init($inifile, $fetch_tests, $retrieve_results, $report_results, $verbose);
    $this->_initialized = TRUE;
    return $this;
  }

  /**
   * Run test and reports
   * 
   * @return \Andou\Automatedpagetest\App
   */
  public function run() {
    if (!$this->_initialized) {
      die('Application not initialized!');
    }
    $this->_echo("Start running");

    $this->checkFolders();

    if ($this->_fetch_tests) {
      $tests_fetched = Fetcher::getInstance($this)
              ->fetchTests($this->_tests_folder);
      Scheduler::getInstance($this)
              ->scheduleTests($tests_fetched, $this->_scheduled_folder);
    }

    $this->_echo("");

    if ($this->_retrieve_results) {
      Checker::getInstance($this)
              ->fetchStatuses($this->_scheduled_folder, $this->_results_folder);
    }

    $this->_echo("");

    if ($this->_report_results) {
      Reporter::getInstance($this)
              ->report($this->_results_folder, $this->_report_folder);
    }

    $this->_echo("");

    return $this;
  }

  /**
   * Cleans working directories
   * 
   * @return \Andou\Automatedpagetest\App
   */
  public function clean() {
    if (!$this->_initialized) {
      die('Application not initialized!');
    }
    $this->_echo("Start cleaning");
    Cleaner::getInstance($this)
            ->cleanFolder($this->_report_folder)
            ->cleanFolder($this->_results_folder)
            ->cleanFolder($this->_scheduled_folder)
            ->cleanFolder($this->_tests_folder);
    $this->_echo("Done cleaning");
    return $this;
  }

  public function install() {
    if (!$this->_initialized) {
      die('Application not initialized!');
    }
    $this->_echo("Installing");
    $this->_echo("");

    $pool_base = BASEPATH;
    $pool_base.="/" . rtrim(ltrim($this->getConfigs()->getPoolBaseFolder(), "/"), "/");
    
    if (!file_exists($pool_base)) {
      mkdir($pool_base);
      $this->_echo("Pool base folder created");
    } else {
      $this->_echo("Pool base folder alredy exists");
    }

    if (!file_exists($this->_report_folder)) {
      mkdir($this->_report_folder);
      $this->_echo("Report folder created");
    } else {
      $this->_echo("Report folder alredy exists");
    }

    if (!file_exists($this->_results_folder)) {
      mkdir($this->_results_folder);
      $this->_echo("Results folder created");
    } else {
      $this->_echo("Results folder alredy exists");
    }

    if (!file_exists($this->_scheduled_folder)) {
      mkdir($this->_scheduled_folder);
      $this->_echo("Sheduled folder created");
    } else {
      $this->_echo("Sheduled folder alredy exists");
    }

    if (!file_exists($this->_tests_folder)) {
      mkdir($this->_tests_folder);
      $this->_echo("Tests folder created");
    } else {
      $this->_echo("Tests folder alredy exists");
    }
    $this->_echo("");
    $this->_echo("Application ready to be installed");
  }

  /**
   * Retrieves some configurations
   * 
   * @return Andou\Inireader\Inireader
   */
  public function getConfigs() {
    return $this->_configs;
  }

  /**
   * Actually initialize the application
   * 
   * @param string $inifile
   * @param boolean $fetch_tests
   * @param boolean $verbose
   */
  protected function _init($inifile, $fetch_tests = TRUE, $retrieve_results = TRUE, $report_results = TRUE, $verbose = TRUE) {
    $this->_verbose = $verbose;
    $this->_fetch_tests = $fetch_tests;
    $this->_retrieve_results = $retrieve_results;
    $this->_report_results = $report_results;
    $this->_configs = Inireader::getInstance($inifile, TRUE);
    $this->_api = Webpagetestwrapper::getInstance($this->getConfigs()->getWebpagetestApiKey());
    $this->_initFolders();
    $this->_echo("");
  }

  /**
   * 
   */
  protected function _initFolders() {
    $this->_tests_folder = BASEPATH;
    $this->_tests_folder.="/" . rtrim(ltrim($this->getConfigs()->getPoolBaseFolder(), "/"), "/");
    $this->_tests_folder.="/" . rtrim(ltrim($this->getConfigs()->getPoolTestFolder(), "/"), "/") . "/";


    $this->_scheduled_folder = BASEPATH;
    $this->_scheduled_folder.="/" . rtrim(ltrim($this->getConfigs()->getPoolBaseFolder(), "/"), "/");
    $this->_scheduled_folder.="/" . rtrim(ltrim($this->getConfigs()->getPoolScheduleFolder(), "/"), "/") . "/";


    $this->_report_folder = BASEPATH;
    $this->_report_folder.="/" . rtrim(ltrim($this->getConfigs()->getPoolBaseFolder(), "/"), "/");
    $this->_report_folder.="/" . rtrim(ltrim($this->getConfigs()->getPoolReportFolder(), "/"), "/") . "/";


    $this->_results_folder = BASEPATH;
    $this->_results_folder.="/" . rtrim(ltrim($this->getConfigs()->getPoolBaseFolder(), "/"), "/");
    $this->_results_folder.="/" . rtrim(ltrim($this->getConfigs()->getPoolResultsFolder(), "/"), "/") . "/";
  }

  protected function checkFolders() {
    if (!is_dir($this->_tests_folder)) {
      $this->_echo('insert a valid test pool folder');
      die();
    }
    if (!is_dir($this->_scheduled_folder)) {
      $this->_echo('insert a valid test schedule folder');
      die();
    }
    if (!is_dir($this->_report_folder)) {
      $this->_echo('insert a valid test report folder');
      die();
    }
    if (!is_dir($this->_results_folder)) {
      $this->_echo('insert a valid test results folder');
      die();
    }
  }

  public function setOutputProvider(\Andou\Shelltools\Outputprovider $op) {
    $this->_output_provider = $op;
  }

  /**
   * Echoes a message in stdout
   * 
   * @param string $message
   * @return \Andou\Automatedpagetest\App
   */
  public function _echo($message) {
    if ($this->_verbose) {
      if (isset($this->_output_provider)) {
        $this->_output_provider->ol($message);
      } else if (defined("IS_SHELL") && IS_SHELL == TRUE) {
        echo $message . "\n";
      } else {
        echo $message . "<br/>";
      }
    }
    return $this;
  }

}

