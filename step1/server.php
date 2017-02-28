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

define("ROOT_PATH", "/tmp");

$serverData = []; // info

//var_dump(stream_get_transports());die();

$url = sprintf("tcp://%s:%s", HTTP_IP, HTTP_PORT);

//如果 stream_socket_accept 超时，就会终止监听，那么只需要继续 stream_socket_accept 即可

echo date("Y-m-d H:i:s"), PHP_EOL;
echo $url, PHP_EOL;

$errno = $errstr = null;
$server = stream_socket_server($url, $errno, $errstr);
if (!$server) {
    logConsole(sprintf("%s (%s)", $errstr, $errno));
    die(255);
}

while (true) {
    // 这里需要处理何时推出
    // stream_socket_accept 超时的时候回产生一个 warning， 这里需要@ 抑制错误
    while ($socket = @stream_socket_accept($server)) {
        global $serverData;
        $serverData = [];
        $request = stream_socket_recvfrom($socket, PACKET_SIZE); // TODO 这里怎么处理（需要接受参数数据）
        $response = parseRequestAndReturnResponse($request);
        fwrite($socket, $response, strlen($response));
        fclose($socket);
    }

    logConsole("loop finish");
}

fclose($server);


function logConsole($msg)
{
    echo sprintf("[%s] %s %s", date("Y-m-d H:i:s"), $msg, PHP_EOL);
}

function parseRequestAndReturnResponse($request)
{
    logConsole("Request：" . $request);

    global $serverData;
    $serverData = parseRequest($request);

    // 解析目录
    $content = "<pre>";

    $path = ROOT_PATH . $serverData['path'];
    logConsole("handle:" . $path);
    if (is_file($path) || is_dir($path)) {
        $iterator = new DirectoryIterator(ROOT_PATH . $serverData['path']);
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->getFilename() == '.') {
                continue;
            }

            if ($fileInfo->isDir()) {
                $content .= sprintf("[%10s] [%-15s] <a href='%s'>%s/</a><br>" . PHP_EOL,
                                    $fileInfo->getSize(),
                                    date("Y-m-d H:i:s", $fileInfo->getMTime()),
                                    $fileInfo->getFilename(),
                                    $fileInfo->getFilename()
                );
            } else {
                $content .= sprintf("[%10s] [%-15s] <a href='%s'>%s</a><br>" . PHP_EOL,
                                    $fileInfo->getSize(),
                                    date("Y-m-d H:i:s", $fileInfo->getMTime()),
                                    $fileInfo->getFilename(),
                                    $fileInfo->getFilename()
                );
            }
        }
    } else {
        $content .= buildPage();
    }


    $content .= "</pre>";

    $header = [
        'Content-Type' => 'text/html; charset=utf-8',
        'Content-Length' => mb_strlen($content),
        'X-Server' => "minbaby/0.1",
    ];

    $response = 'HTTP/1.1 200 OK' . CRLF
                 . implodeKeyValue($header) .CRLF
                 . $content;

//    logConsole("response:" . $response);
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

function buildPage()
{
    global $serverData;
    return sprintf("<h1>Time: %s-%s</h1>", time(), array_get($serverData, 'get.index', 'null'));
}

/*
 * 以换行分割，第一行必定为 ACTION PATH HTTP.VERSION
 */
function parseRequest($request)
{
    $arr = array_filter(explode(CRLF, $request));
    $ci = [];

    //parse 第一条
    list($ci['method'], $ci['path'], $ci['version']) = explode(" ", $arr[0]);
    array_shift($arr);

    foreach ($arr as $value) {
        list($key, $val) = explode(":", $value);
        if ($key == 'Cookie') {
            // 特殊处理
            $tmp = explode(";", $val);
            foreach ($tmp as $item) {
                list($k, $v) = explode("=", $item);
                $ci['cookie'][trim($k)] = trim($v);
            }
            continue;
        }
        $ci[trim($key)] = trim($val);
    }

    foreach ($ci as $key => &$val) {
        is_string($val) && trim($val);
        // 解析 path
        if ($key == 'path' && false !== strpos($val, '?')) {
            list($_, $param) = explode("?", $val);
            foreach (explode("&", $param) as $item) {
                list($k, $v) = explode("=", $item);
                $ci['get'][$k] = $v;
            }
        }
    }
    return $ci;
}

function array_get($arr, $key, $default = '')
{
    if (is_null($key)) {
        return $default;
    }

    if (isset($arr[$key])) {
        return $arr[$key];
    }

    $segs = explode('.', $key);
    while ($seg = array_shift($segs)) {
        if (isset($arr[$seg])) {
            $arr = $arr[$seg];
        } else {
            return $default;
        }
    }

    return $arr;
}
