<?php


// 一个 event-manager == event_base
// 一个 event

@cli_set_process_title("[PHP]pecl-event");
$server1 = stream_socket_server("tcp://127.0.0.1:9876");
$server2 = stream_socket_server("tcp://127.0.0.1:9875");
stream_set_blocking($server1, false);
stream_set_blocking($server2, false);

$eventManager = new EventBase();

// 这里使用死循环的原因在于，我们还需要在循环开始之后进行一些处理，比如。。增加事件之类的
// 如果可以确定不需要其他的处理，可以只使用时间循环。
init($eventManager, $server1, $server2); // 这里的是持续事件，不是一次性事件
while (true) {
    $eventManager->loop(EventBase::LOOP_ONCE);
}

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

function init($eventManager, $server1, $server2)
{
    $event1 = new Event($eventManager, $server1, Event::READ | Event::PERSIST, function ($fd, $what, $args) {
        readAndClose($fd);
    });

    $event2 = new Event($eventManager, $server2, Event::READ | Event::PERSIST, function ($fd, $what, $args) {
        readAndClose($fd);
    });

    $event3 = new Event($eventManager, -1, Event::TIMEOUT | Event::PERSIST, function ($args) {
        echo "timer\n";
    });

    $event1->add();
    $event2->add();
    $event3->add(0.1);
}
