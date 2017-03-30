<?php

namespace Minbaby\Loop;

interface Loop
{
    /**
     * run
     * @return void
     */
    public function run();

    public function stop();

    public function addTimer(float $interval, callable $callback, bool $isPeriodic = false): \Event;

    public function removeEvent(\Event $timeEvent);

    public function addReadEvent($resourceFd, callable $callback): \Event;

    public function addWriteEvent($resourceFd, callable $callback): \Event;
}
