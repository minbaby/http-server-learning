<?php

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

socket_connect($socket, 'localhost', 9876);

foreach (range(0, 10) as $item) {
    $data = "?????+++";
    $d = pack('ia*', strlen($data), $data);
    socket_write($socket, $d, strlen($d));
    sleep(1);
}

socket_close($socket);
