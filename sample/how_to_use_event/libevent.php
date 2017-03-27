<?php

// src
// https://bitbucket.org/osmanov/pecl-event

$cfg = new EventConfig();
$cfg->avoidMethod('kqueue');

$event = new EventBase($cfg);

echo posix_getpid(), PHP_EOL;

$ev = Event::signal($event, SIGUSR2, function ($sig) {
    echo $sig, PHP_EOL;
});

$ev->setTimer($event, function ($v) {
    echo $v,PHP_EOL;
});

var_dump(Event::getSupportedMethods());
var_dump($event->getMethod());
var_dump($ev->pending(Event::READ));
var_dump($event->getTimeOfDayCached());
var_dump(microtime(1));

$ev->add(5);

$event->loop();
//$event->dispatch();
