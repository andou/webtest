<?php

namespace Andou\Automatedpagetest;

use Andou\Exceller\Exceller;

class Reporter {

  /**
   *
   * @var \Andou\Automatedpagetest\App 
   */
  protected $_app;

  /**
   * 
   * @var \Andou\Exceller\Exceller
   */
  protected $_exceller;

  /**
   * Returns an instance of this class
   * 
   * @return \Andou\Automatedpagetest\Reporter
   */
  public static function getInstance($app) {
    $classname = __CLASS__;
    return new $classname($app);
  }

  public function __construct($app) {
    $this->_app = $app;
    $this->_exceller = new Exceller();
  }

  public function report($results_folder, $reports_folder) {
    $paths = array();
    $this->_app->_echo("Generating Reports");
    $this->_exceller
            ->setSavePath($reports_folder)
            ->setFileName("test_" . date("Y_m_d_H_i_s"));

    $this->_exceller->insertHeaderCell("A", 1, "Url");
    $this->_exceller->insertHeaderCell("B", 1, "Location");
    $this->_exceller->insertHeaderCell("C", 1, "Browser");
    $this->_exceller->insertHeaderCell("D", 1, "Connectivity");
    $this->_exceller->insertHeaderCell("E", 1, "Runs");
    $this->_exceller->insertHeaderCell("F", 1, "Summary");
    $this->_exceller->insertHeaderCell("G", 1, "TTFB - First");
    $this->_exceller->insertHeaderCell("H", 1, "Start Render - First");
    $this->_exceller->insertHeaderCell("I", 1, "Fully Loaded - First");
    $this->_exceller->insertHeaderCell("J", 1, "TTFB - Repeat");
    $this->_exceller->insertHeaderCell("K", 1, "Start Render - Repeat");
    $this->_exceller->insertHeaderCell("L", 1, "Fully Loaded - Repeat");

    \PHPExcel_Shared_Font::setAutoSizeMethod(\PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
    $this->_exceller->setAutosizeRange("A", "L");

    $files = scandir($results_folder);
    $row = 2;

    foreach ($files as $file) {
      if (preg_match("/^test(.+).json$/", $file)) {
        $filepath = $results_folder . $file;
        $test_result = json_decode(file_get_contents($filepath), TRUE);

        $data = array();
        $_location = explode(":", $test_result['data']['location']);
        $browser = isset($_location[1]) ? $_location[1] : 'default';
        $location = isset($_location[0]) ? $_location[0] : 'default';
        $data[] = $test_result['data']['url'];
        $data[] = $location;
        $data[] = $browser;
        $data[] = $test_result['data']['connectivity'];
        $data[] = count($test_result['data']['runs']);
        $data[] = $test_result['data']['summary'];
        $data[] = $test_result['data']['average']['firstView']['TTFB'];
        $data[] = $test_result['data']['average']['firstView']['render'];
        $data[] = $test_result['data']['average']['firstView']['loadTime'];
        $data[] = $test_result['data']['average']['repeatView']['TTFB'];
        $data[] = $test_result['data']['average']['repeatView']['render'];
        $data[] = $test_result['data']['average']['repeatView']['loadTime'];
        $this->_insertRow($row, $data);
        $paths[] = $filepath;
        $row++;
      }
    }
    $this->_exceller->finalize();

    foreach ($paths as $p) {
      unlink($p);
    }
  }

  protected function _insertRow($row, $data) {
    $cnt = 0;
    foreach ($data as $_data) {
      $this->_exceller->insertCell($this->_exceller->getLetter($cnt), $row, $_data);
      $cnt++;
    }
  }

}