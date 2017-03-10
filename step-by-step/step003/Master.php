<?php

class Master
{
    const PROCESS_MAX = 4;

    protected $processFixedArray;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var string
     */
    private $port;

    const DATE_FORMAT = "Y-m-d H:i:s";

    protected $server;

    public function __construct($ip = '127.0.0.1', $port = '9876')
    {
        $this->ip = $ip;
        $this->port = $port;

        @cli_set_process_title("HttpMaster");

        $this->init();
        $this->processFixedArray = new SplFixedArray(static::PROCESS_MAX);
        for ($i = 0; $i < static::PROCESS_MAX; $i++) {
            $this->processFixedArray[$i] = new Process(new HttpWorker($this->server));
        }
    }

    private function init()
    {
        $url = sprintf("tcp://%s:%s", $this->ip, $this->port);
        $this->logConsole(__FUNCTION__, $url);
        $this->server = stream_socket_server($url, $errno, $errstr);
        if (!$this->server) {
            $errMsg = sprintf("%s (%s)", $errstr, $errno);
            $this->logConsole(__FUNCTION__, $errMsg);
            throw new Exception($errMsg);
        }
    }

    public function run()
    {
        for ($i = 0; $i < static::PROCESS_MAX; $i++) {
            $this->processFixedArray[$i]->start();
        }
    }

    public function join()
    {
        pcntl_wait($status);
        fclose($this->server);
    }

    private function logConsole($type, $msg)
    {
        echo sprintf("[%s] [%s] %s %s", date("Y-m-d H:i:s"), $type, $msg, PHP_EOL);
    }
}
