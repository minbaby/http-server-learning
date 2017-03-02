<?php

$f = "../../static/httpd_proc.jpg";

if (!file_exists($f)) {
    die("file not exists");
}

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

socket_connect($socket, 'localhost', 9876);

// 发送 & 接受 缓冲区
// socket_set_option($socket, SOL_SOCKET, SO_SNDBUF, 1);
// echo socket_get_option($socket, SOL_SOCKET, SO_SNDBUF), '-snd', PHP_EOL;
// echo socket_get_option($socket, SOL_SOCKET, SO_RCVBUF), '-rcv', PHP_EOL;

$fileInfo = new SplFileInfo($f);

echo $fileInfo->getSize();

$bSize = pack('i', $fileInfo->getSize());

socket_write($socket, $bSize, strlen($bSize));

$img = file_get_contents($f);
socket_write($socket, $img, strlen($img));


//socket_close($socket);
