<?php

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

if ($socket === false) {
    $errorCode = socket_last_error($socket);
    $errorMessage = socket_strerror($errorCode);
    echo sprintf("(%s) %s", $errorCode, $errorMessage);
    die(-1);
}

$flags = socket_bind($socket, '127.0.0.1', 9876);

if ($flags == false) {
    $errorCode = socket_last_error($socket);
    $errorMessage = socket_strerror($errorCode);
    echo sprintf("(%s) %s", $errorCode, $errorMessage);
    die(-2);
}

$flags = socket_listen($socket, 1);
if ($flags == false) {
    $errorCode = socket_last_error($socket);
    $errorMessage = socket_strerror($errorCode);
    echo sprintf("(%s) %s", $errorCode, $errorMessage);
    die(-2);
}

// 这里如果 accept 之后，是可以无限 get 数据的，直到 socket close
while (($accettSocket = socket_accept($socket)) !== false) {
    echo time(), PHP_EOL;
    socket_write($accettSocket, 'HEHE', strlen('HEHE'));

    $total = '';
    $len = 0;
    while (true) {
        // 1读取字节
        $str = socket_read($accettSocket, 1);
        if (false === $str) {
            $errorCode = socket_last_error($socket);
            $errorMessage = socket_strerror($errorCode);
            echo sprintf("(%s) %s", $errorCode, $errorMessage);
            die(-2);
        }

        $total .= $str;
        if (strlen($str) == 0) {
            break;
        } elseif (strlen($total) < 4) {
            continue;
        } elseif (strlen($total) == 4) {
            $ret = unpack('ilen/', $total);
            $len = $ret['len'];
        } elseif (strlen($total) == $len + 4) {
            // 读完了，输出；
            echo substr($total, 4), "-Done", PHP_EOL;
            $total = '';
            $len = 0;
        } else {
            echo "FUCK-ELSE", PHP_EOL;
        }
    }
    echo "Done...", PHP_EOL;
}

socket_close($socket);
