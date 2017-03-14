<?php

namespace Minbaby;

use Exception;
use Minbaby\HttpParse\ParseRequest;
use Minbaby\HttpParse\Response;

class Application
{
    protected $ip;

    protected $port;

    protected $type = 'app';

    protected $server;

    protected $timeOut = 1000000;

    public function run($ip = '127.0.0.1', $port = '9876')
    {
        $this->ip = $ip;
        $this->port = $port;

        $this->init();
        $this->loop();
        $this->close();
    }

    private function init()
    {
        $url = sprintf("tcp://%s:%s", $this->ip, $this->port);
        Logger::log($this->type, 'listen:' . $url);
        $this->server = stream_socket_server($url, $errno, $errstr);
        if (!$this->server) {
            $errMsg = sprintf("%s (%s)", $errstr, $errno);
            Logger::log($this->type, $errMsg);
            throw new Exception($errMsg);
        }
    }

    private function loop()
    {
        $dispatch = new Dispatch();
        $writes = $excepts = null;
        while (true) {
            try {
                $reads = [$this->server];
                $count = stream_select($reads, $writes, $excepts, 0, $this->timeOut);
                if ($count === false) {
                    throw new Exception("[stream_select] error");
                } elseif ($count > 0) {
                    foreach ($reads as $read) {
                        $socket = stream_socket_accept($read, 0);
                        $string = stream_socket_recvfrom($socket, 1024);
                        $request = (new ParseRequest($string))->parse();
                        $content = $dispatch->run($request);
                        stream_socket_sendto($socket, $content);
                        $name = stream_socket_get_name($read, false);
                        Logger::log($this->type, "[get] [$name] " . $string);
                        fclose($socket);
                    }
                } else {
                    Logger::log($this->type, '[stream_select] do nothing');
                }
            } catch (Exception $exception) {
                Logger::log($this->type, "[500] " . $exception->getMessage());
            } finally {
                Logger::log($this->type, "loop finished");
            }
        }
    }

    private function close()
    {
        if ($this->server) {
            fclose($this->server);
        }
    }
}
