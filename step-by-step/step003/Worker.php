<?php

abstract class Worker
{
    /**
     * @return mixed
     */
    abstract public function run();

    abstract public function getWorkerName();
}
