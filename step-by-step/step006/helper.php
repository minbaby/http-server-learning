<?php


function readAndClose($serverSocket)
{
    $socket = stream_socket_accept($serverSocket, 0);

    $string = stream_socket_recvfrom($socket, 1024);
    $name = stream_socket_get_name($serverSocket, false);
    logInfo($string . ' <==> ' . $name);

    $str = sprintf("I got [%s]", $string);
    fwrite($socket, $str, strlen($str));

    fclose($socket);
}

function logInfo($msg)
{
    echo sprintf("[loop] [%s] [%s] \n", date("Y-m-d H:i:s"), $msg);
}
