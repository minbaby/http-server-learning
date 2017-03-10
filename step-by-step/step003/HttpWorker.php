<?php

class HttpWorker extends Worker
{

    const PACKET_SIZE = 1500;

    protected $serverData = [];

    const ROOT_PATH = '/tmp/';

    /**
     * @var
     */
    private $socketServer;

    public function __construct($socketServer)
    {
        $this->socketServer = $socketServer;
    }

    public function run()
    {
        while (true) {
            try {
                while ($socket = @stream_socket_accept($this->socketServer)) {
                    $this->serverData = [];
                    $requestData = stream_socket_recvfrom($socket, static::PACKET_SIZE);
                    $responseData = $this->parseRequestAndReturnResponse($requestData);
                    fwrite($socket, $responseData, strlen($responseData));
                    fclose($socket);
                }
            } catch (Exception $exception) {
                $this->log("[500] " . $exception->getMessage());
            } finally {
                $this->log("loop finished");
            }
        }
    }

    public function getWorkerName()
    {
        return __CLASS__;
    }

    private function log($msg)
    {
        echo sprintf("[%s] %s %s", date("Y-m-d H:i:s"), $msg, PHP_EOL);
    }

    private function parseRequestAndReturnResponse($requestData)
    {
        $this->log("Request: " . $requestData);
        $this->parseRequest($requestData);

        $content = $this->dispatch();

        $header = [
            'Content-Type'   => 'text/html; charset=utf-8',
            'Content-Length' => strlen($content),
            'X-Server'       => "minbaby/0.3",
        ];

        $response = 'HTTP/1.1 200 OK' . CRLF . implodeKeyValue($header) . CRLF . $content;

        return $response;
    }

    private function parseRequest($requestData)
    {
        $arr = array_filter(explode(CRLF, $requestData));
        $ci = [];

        //parse 第一条
        list($ci['method'], $ci['path'], $ci['version']) = explode(" ", $arr[0]);
        array_shift($arr);
        $ci['path'] = rtrim($ci['path'], '/');

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
        $this->serverData = $ci;
    }

    private function dispatch()
    {
        $path = static::ROOT_PATH . ltrim($this->serverData['path'], '/');
        $this->log("handle: " . $path);
        $content = '';
        if (is_dir($path)) {
            $content = "<pre>";
            $iterator = new DirectoryIterator(static::ROOT_PATH . $this->serverData['path']);
            foreach ($iterator as $fileInfo) {
                if ($fileInfo->getFilename() == '.') {
                    continue;
                }

                if ($fileInfo->isDir()) {
                    $url = implode(DIRECTORY_SEPARATOR, [$this->serverData['path'], $fileInfo->getFilename()])
                           . ($fileInfo->isDir() ? "/" : "");
                } else {
                    $url = $fileInfo->getFilename();
                }

                $content .= sprintf("[%10s] [%-15s] <a href='%s'>%s%s</a><br>\n", $fileInfo->getSize(),
                                    date("Y-m-d H:i:s", $fileInfo->getMTime()), $url, $fileInfo->getFilename(),
                                    $fileInfo->isDir() ? "/" : ""
                );
            }
            $content .= "</pre>";
        } elseif (is_file($path)) {
            $content = file_get_contents($path);
        } else {
            $content .= sprintf("<h1>Time: %s (index=%s)</h1>", time(),
                                array_get($this->serverData, 'get.index', 'null')
            );
        }

        return $content;
    }
}
