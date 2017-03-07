<?php

require_once 'const.php';
require_once 'helper.php';
require_once 'Application.php';

try {
    $app = new Application();
    $app->run();
} catch (Exception $e) {
    echo $e->getMessage(), PHP_EOL;
}