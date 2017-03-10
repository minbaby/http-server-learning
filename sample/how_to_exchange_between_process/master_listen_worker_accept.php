<?php

// 这样写可能会出现惊群效应，我们学习用，不考虑这个情况！！！

define("T", 100 * 1000);

function logConsole($type, $msg)
{
    echo sprintf("[%s] [%s] %s %s", date("Y-m-d H:i:s"), $type, $msg, PHP_EOL);
}


$errno = $errstr = null;
$server = stream_socket_server("tcp://127.0.0.1:9876", $errno, $errstr);
if (!$server) {
    logConsole("before fork", sprintf("%s (%s)", $errstr, $errno));
    die(255);
}

function worker($server, $i)
{
    while (true) {
        while ($socket = @stream_socket_accept($server)) {
            global $serverData;
            $serverData = [];
            $request = stream_socket_recvfrom($socket, 1024);
            logConsole("child-{$i}", "get:" . $request);
            $response = "time:{$i}:" . time();
            fwrite($socket, $response, strlen($response));
            fclose($socket);
        }

        logConsole("child", "loop finish");
    }
}


$pid = pcntl_fork();

if ($pid < 0) {
    echo "fuck this error";
} elseif ($pid == 0) {
    // 子进程
    @cli_set_process_title("child");
    worker($server, 0);
} else {
    // 父进程
    @cli_set_process_title("parent");

    $i = 0;
    foreach (range(0, 3) as $item) {
        $i++;
        $pid = pcntl_fork();
        if ($pid == 0) {
            worker($server, $i);
        } elseif ($pid < 0) {
            echo "fuck this error";
        } else {
            // parent
        }
    }

    pcntl_wait($status);
    fclose($server);
}
