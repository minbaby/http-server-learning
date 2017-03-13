<?php
require_once 'Process.php';
require_once "Worker.php";
require_once "HttpWorker.php";
require_once "Master.php";
require_once "helper.php";
require_once "const.php";
require_once 'Logic.php';
require_once 'Logger.php';

$master = new Master($ip = '127.0.0.1', $port = '9876');
$master->run();
$master->join();
