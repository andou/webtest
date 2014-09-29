<?php

require_once './vendor/autoload.php';

new Andou\Autoloader\Autoloader(__DIR__);

$app = Andou\Automatedpagetest\App::getInstance()
        ->init(INIFILE, FALSE);
//$app->clean();
$app->run();
