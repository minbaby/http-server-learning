<?php

class HttpWorker extends Worker
{

    const PACKET_SIZE = 1500;

    protected $serverData = [];


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
                    $responseData = (new Logic($requestData))->parseRequestAndReturnResponse();
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
        return '[PHP]' . __CLASS__;
    }

    private function log($string)
    {
        Logger::log(__CLASS__, $string);
    }
}
