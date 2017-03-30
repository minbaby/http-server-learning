<?php

$i = 0;

while (true) {
    $client = stream_socket_client("tcp://localhost:987" . ($i % 2 == 0 ? 5 : 6));
    stream_socket_sendto($client, sprintf("[%5d]", $i));
    $i++;

    $str = stream_socket_recvfrom($client, 1024);
    echo sprintf("[%s] [%s]\n", microtime(true), $str);

    fclose($client);
    usleep(1000 * 500);
}
