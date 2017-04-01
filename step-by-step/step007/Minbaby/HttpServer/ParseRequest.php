<?php

namespace Minbaby\HttpServer;

class ParseRequest
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * @return \Minbaby\HttpServer\Request
     * @throws \Exception
     */
    public function parse()
    {
        $request = new Request();

        $lines = explode(CRLF, $this->request);

        if (count($lines) < 1) {
            throw new \Exception("请求至少要有一行数据");
        }

        $firstLine = array_shift($lines);
        list($method, $path, $protocol) = explode(" ", $firstLine);

        $request->setMethod($method);
        $request->setPath($path);
        $request->setProtocol($protocol);

        // 似乎一行中可能存在多个冒号
        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            $explode = explode(":", $line, 2);
            if (count($explode) != 2) {
                throw new \Exception("header exception");
            }
            list($name, $value) = explode(":", $line);
            $request->getHeaders()->set(trim($name), trim($value));
        }

        return $request;
    }
}
