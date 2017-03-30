<?php

include "Loop.php";
include "EventLoop.php";
include "helper.php";

$loop = new \Minbaby\Loop\EventLoop();

$event1 = $loop->addTimer(0.1, function () {
    echo microtime(true), PHP_EOL;
}, true);

$loop->addTimer(5, function () use ($loop, $event1) {
//    $loop->stop();
    echo "干掉 event1", PHP_EOL;
    $loop->removeEvent($event1);
});

@cli_set_process_title("[PHP]pecl-event event-loop");
$server1 = stream_socket_server("tcp://127.0.0.1:9876");
$server2 = stream_socket_server("tcp://127.0.0.1:9875");
stream_set_blocking($server1, false);
stream_set_blocking($server2, false);

$loop->addReadEvent($server1, function ($fd, $what, $args) {
    readAndClose($fd);
});

$loop->addReadEvent($server2, function ($fd, $what, $args) {
    readAndClose($fd);
});


$loop->run();
