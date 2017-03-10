<?php

class Process
{
    private $pid;

    /**
     * @var Worker
     */
    private $worker;

    public function __construct(Worker $worker)
    {
        $this->worker = $worker;
    }

    public function start()
    {
        $pid = pcntl_fork();
        if ($pid < 0) {
            echo "fuck this error";
            exit(1);
        } elseif ($pid == 0) {
            @cli_set_process_title($this->worker->getWorkerName());
            // 子进程
            $this->worker->run();
            $this->logConsole(__FUNCTION__, sprintf("[child] pid:%s, ppid:%s", posix_getpid(), posix_getppid()));
            exit(0); // 这里很重要。。。一定要退出
        } else {
            $this->logConsole(__FUNCTION__, sprintf("[parent] pid:%s, cpid:%s", posix_getpid(), $pid));
        }
    }

    private function logConsole($type, $msg)
    {
        echo sprintf("[%s] [%s] %s %s", date("Y-m-d H:i:s"), $type, $msg, PHP_EOL);
    }
}
