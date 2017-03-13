<?php




while (true) {
    $client = stream_socket_client("tcp://localhost:9876");
    stream_socket_sendto($client, time());
    usleep(1000 * 500);
}
