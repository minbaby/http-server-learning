<?php

// 不知道为什么 OSX 上会丢请求数据。

@cli_set_process_title("[PHP]stream_select");
$server1 = stream_socket_server("tcp://127.0.0.1:9876");
$server2 = stream_socket_server("tcp://127.0.0.1:9875");
stream_set_blocking($server1, false);
stream_set_blocking($server2, false);

$except = $write = null;

do {
    // 需要重新填充
    $read = $write = [$server1, $server2];
    $flag = @stream_select($read, $write, $except, 0, 1000 * 1000);
    if ($flag === false) {
        logInfo("错误错误");
        break;
    } elseif ($flag > 0) { // 共有几个可操作
        foreach ($read as $r) {
            $socket = stream_socket_accept($r, 0);
            fwrite($socket, "a", 1);

            $string = stream_socket_recvfrom($socket, 1024);
            $name = stream_socket_get_name($r, false);
            logInfo($string . ' <==> ' . $name);
            fclose($socket);
        }
    } else {
        logInfo("这里是，既没有发生错误， 也没有可读数据");
    }
} while (true);


function logInfo($msg)
{
    echo sprintf("[loop] [%s] [%s] \n", date("Y-m-d H:i:s"), $msg);
}
