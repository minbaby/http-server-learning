<?php
/*
 * 基本思路
 *
 * 需要一个死循环处理连接请求
 *
 * 循环中处理请求数据，响应数据
 *
 * 所以： 这是 单进程+阻塞
 *
 */


define("HTTP_IP", "127.0.0.1");     // 绑定 ip
define("HTTP_PORT", "9876");        // 端口
define("PACKET_SIZE", 1500);
define("CRLF", "\r\n");


//var_dump(stream_get_transports());die();

$url = sprintf("tcp://%s:%s", HTTP_IP, HTTP_PORT);

while (true) {
    echo date("Y-m-d H:i:s"), PHP_EOL;
    echo $url, PHP_EOL;

    $errno = $errstr = null;
    $server = stream_socket_server($url, $errno, $errstr);

    if (!$server) {
        logConsole(sprintf("%s (%s)", $errstr, $errno));
        die(255);
    }

    while ($socket = stream_socket_accept($server)) {
        $str = "";
        $str = stream_socket_recvfrom($socket, PACKET_SIZE); // 这里怎么处理（需要接受参数数据）

        logConsole($str);

        $ss = handleData($str);

        logConsole("response:" . $ss);
        fwrite($socket, $ss, strlen($ss));

        fclose($socket);
    }

    stream_socket_shutdown($server, STREAM_SHUT_RDWR);
}

function logConsole($msg)
{
    echo sprintf("[%s] %s %s", date("Y-m-d H:i:s"), $msg, PHP_EOL);
}

function handleData($content)
{
    $response = 'HTTP/1.1 200 OK' . CRLF;

    $header = [
        'Content-Type' => 'text/html; charset=utf-8',
        'X-Server' => "minbaby/0.1",
    ];

    $response .= implodeKeyValue($header);

    return $response;
}

function implodeKeyValue($data)
{
    $str = '';
    foreach ($data as $key => $value) {
        $str .= sprintf("%s:%s " . CRLF, $key, $value);
    }

    return $str;
}