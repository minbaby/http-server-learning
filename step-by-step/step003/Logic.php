<?php

/**
 * 这里放着 http 处理
 */
class Logic
{
    protected $serverData;

    private $requestData;

    /**
     * Logic constructor.
     *
     * @param $requestData
     */
    public function __construct($requestData)
    {
        $this->requestData = $requestData;
    }

    const ROOT_PATH = '/tmp/';

    public function parseRequestAndReturnResponse()
    {
        $this->log("Request: " . $this->requestData);
        $this->parseRequest($this->requestData);

        $content = $this->dispatch();

        $header = [
            'Content-Type'          => 'text/html; charset=utf-8',
            'Content-Length'        => strlen($content),
            'X-Server'              => "minbaby/0.3",
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

                $content .= sprintf(
                    "[%10s] [%-15s] <a href='%s'>%s%s</a><br>\n",
                    $fileInfo->getSize(),
                    date("Y-m-d H:i:s", $fileInfo->getMTime()),
                    $url,
                    $fileInfo->getFilename(),
                    $fileInfo->isDir() ? "/" : ""
                );
            }
            $content .= "</pre>";
        } elseif (is_file($path)) {
            $content = file_get_contents($path);
        } else {
            $content .= sprintf(
                "<h1>Time: %s (index=%s)</h1>",
                time(),
                array_get($this->serverData, 'get.index', 'null')
            );
        }

        return $content;
    }

    private function log($string)
    {
        Logger::log(__CLASS__, $string);
    }
}
