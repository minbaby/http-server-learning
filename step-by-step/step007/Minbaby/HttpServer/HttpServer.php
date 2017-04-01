<?php

namespace Minbaby\HttpServer;

use Event;
use Exception;

class HttpServer
{
    protected $server;

    /**
     * @var \Minbaby\HttpServer\Dispatch
     */
    protected $dispatch;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var int
     */
    private $port;

    /**
     * @var \EventBase
     */
    private $eventBase;

    /**
     * @var \SplObjectStorage
     */
    private $events;

    public function __construct($ip = '127.0.0.1', $port = 8080)
    {
        MemoryMonitor::init();
        $this->ip = $ip;
        $this->port = $port;
    }


    public function run()
    {
        Logger::log(__CLASS__, "=========================run=========================");

        $this->init();
        $this->loop();
        $this->close();
    }

    private function init()
    {
        $url = sprintf("tcp://%s:%s", $this->ip, $this->port);
        Logger::log(__CLASS__, 'listen on: ' . $url);
        $this->server = stream_socket_server($url, $errno, $errstr);
        if (!$this->server) {
            $errMsg = sprintf("%s (%s)", $errstr, $errno);
            Logger::log(__CLASS__, $errMsg);
            throw new Exception($errMsg);
        }

        $this->eventBase = new \EventBase();
        $this->events = new \SplObjectStorage();
        $this->dispatch = new Dispatch();

        $event = new Event($this->eventBase, -1, Event::PERSIST | Event::TIMEOUT, function () {
            Logger::log(__CLASS__, MemoryMonitor::prettyPrint(true));
        });
        $event->add(1);
        $this->events->attach($event);
    }

    private function loop()
    {
        $event = new Event($this->eventBase, $this->server, Event::READ | Event::PERSIST, function ($fd, $what, $args) {
            $this->onData($fd, $what, $args);
        });
        $event->add();
        $this->events->attach($event);
        $this->eventBase->dispatch();
    }

    private function close()
    {
        if ($this->server) {
            fclose($this->server);
        }
    }

    private function onData($fd, $what, $args)
    {
        $socket = null;
        try {
            $socket = stream_socket_accept($fd, 0);
            if (!$socket) {
                Logger::log(__CLASS__, "accept error");
                return;
            }
            $string = stream_socket_recvfrom($socket, 1024);
            $request = (new ParseRequest($string))->parse();
            $content = $this->dispatch->run($request);
            stream_socket_sendto($socket, $content);
        } catch (Exception $ex) {
            Logger::log(__CLASS__, $ex->getMessage());
        } catch (\Error $error) {
            Logger::log(__CLASS__, $error->getMessage());
        } finally {
            empty($socket) || fclose($socket);
        }
    }
}
