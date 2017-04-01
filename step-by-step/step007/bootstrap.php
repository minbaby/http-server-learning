<?php

//function load($dir = '.')
//{
//    $iterator = new DirectoryIterator($dir);
//    foreach ($iterator as $item) {
//        if ($item->isDot() || $item->getFilename() == 'bootstrap.php') {
//            continue;
//        }
//
//        if ($item->isDir()) {
//            load($item->getRealPath());
//        }
//
//        if ($item->isFile() && $item->getExtension() == "php") {
//            include $item->getRealPath();
//            echo "include: ", $item->getRealPath(), PHP_EOL;
//        } else {
//            echo "ignore: ", $item->getRealPath(), PHP_EOL;
//        }
//    }
//}
//
//load();

require __DIR__ . '/vendor/autoload.php';

$httpServer = new \Minbaby\HttpServer\HttpServer();
$httpServer->run();
