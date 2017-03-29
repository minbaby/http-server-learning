<?php

// src
// https://bitbucket.org/osmanov/pecl-event

$cfg = new EventConfig();
$cfg->avoidMethod('kqueue');

$eventBase = new EventBase($cfg);

echo posix_getpid(), PHP_EOL;

$index = 0;

$event = new Event($eventBase, -1, Event::TIMEOUT | Event::PERSIST, function () use (&$start, &$index, &$event) {
    echo (microtime(true) - $start), PHP_EOL;
    posix_kill(posix_getpid(), SIGUSR2);
    $index++;
    if ($index == 4) {
        $event->del();
    }
});
$event->add(1);

$event3 = Event::signal($eventBase, SIGUSR2, function ($sig) {
    echo time(), "-", $sig, PHP_EOL;
});
$event3->add(10);

$start = microtime(true);
echo $start, PHP_EOL;
$loopFlags = $eventBase->loop();

echo "Loop:" . strval($loopFlags) , PHP_EOL;