<?php

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

socket_connect($socket, 'localhost', 9876);

// 发送 & 接受 缓冲区
// socket_set_option($socket, SOL_SOCKET, SO_SNDBUF, 1);
// echo socket_get_option($socket, SOL_SOCKET, SO_SNDBUF), '-snd', PHP_EOL;
// echo socket_get_option($socket, SOL_SOCKET, SO_RCVBUF), '-rcv', PHP_EOL;

// 一字节，一字节写数据，server_read_one_byte 是可以处理这种情况
foreach (range(0, 3) as $item) {
    $data = "我是测试你懂得" . sha1(json_encode([rand(1000, 2000) => rand(0, 100000)]));
    $d = pack('ia*', strlen($data), $data); // 这里返回的是字节数组
    socket_write($socket, $d, strlen($d));

    for ($i=0; $i<strlen($d); $i++) {
        socket_write($socket, $d[$i], 1);
        usleep(200 * 1000);
    }
}

socket_close($socket);
