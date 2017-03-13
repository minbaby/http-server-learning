<?php

class Logger
{
    public static function log($type, $msg)
    {
        echo sprintf("[%s] [%s] %s %s", date("Y-m-d H:i:s"), $type, $msg, PHP_EOL);
    }
}
