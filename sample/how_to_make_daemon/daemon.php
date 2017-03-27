<?php

$pidFile = '/tmp/daemon.pid';

//这里应该用文件锁，而不是判断文件是否存在

$action = '';
isset($argv[1]) && $action = $argv[1];

if (empty($action)) {
    echo "php daemon.php (start|stop)\n";
    exit(0);
}

if (file_exists($pidFile) && $action == 'start') {
    echo "service running.", PHP_EOL;
    exit(0);
}

if (!file_exists($pidFile) && $action == 'stop') {
    echo "pid not exists.", PHP_EOL;
    exit(0);
}

if ($action == 'start') {
    umask(0); // 设置权限
    $pid = pcntl_fork();
    if ($pid < 0) {
        echo "error";
    } elseif ($pid == 0) {
        posix_setsid();// 放弃 tty 控制权， create a new session for a process， 然后成为该session 的 leader
        chdir("/"); // 切换工作目录到 root 目录，释放当前目录
        fclose(STDIN); // 关闭自己标准流
        fclose(STDOUT);
        fclose(STDERR);

        // 子进程
        while (true) {
            usleep(1000 * 500);
            syslog(LOG_NOTICE, "Test");
            pcntl_signal_dispatch();
        }
    } else {
        // 父进程
        echo "service start. pid = {$pid}", PHP_EOL;
        file_put_contents($pidFile, $pid);
    }
} elseif ($action == 'stop') {
    $pid = file_get_contents($pidFile);
    echo "pid = {$pid} stop", PHP_EOL;
    posix_kill($pid, SIGTERM);
    unlink($pidFile);
}
