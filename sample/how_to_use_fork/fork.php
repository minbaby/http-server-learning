<?php

// 注意不同系统下，相同常量对应的数字是不一样的。
// 这里处理 fork 一个子进程， 然后由父进程处理进程。


$signs = [
    SIGUSR1 => 'SIGUSR1',
    SIGUSR2 => 'SIGUSR2',
    SIGCHLD => 'SIGCHLD',
    SIGALRM => 'SIGALRM'
];


function register()
{
    global $signs;
    foreach ($signs as $k => $v) {
        pcntl_signal($k, "sig_handler");
    }
}


function sig_handler($sig)
{
    global $signs;
    echo sprintf("sig-%s-%s", $sig, $signs[$sig]), PHP_EOL;
}


$pid = pcntl_fork();
if ($pid === 0) {
    echo "子", "-pid-", posix_getpid(), PHP_EOL;

    $pids[] = posix_getpid();
    @cli_set_process_title("children"); // osx 不支持
    register();

    while (true) {
        echo 'do-', time(), PHP_EOL;
//        usleep(500 * 1000);
        sleep(1);
        pcntl_alarm(1);
        posix_kill(posix_getpid(), SIGUSR1);
        pcntl_signal_dispatch();
    }
} elseif ($pid === -1) {
    echo 'ERROR', pcntl_get_last_error();
} else {
    @cli_set_process_title("parent"); // osx 不支持
    echo "父", "-pid-", posix_getpid(), PHP_EOL;
    $status = "";
    pcntl_wait($status); // 需要等待子进程， 防止子进程变成僵尸进程。
    exit;
}
