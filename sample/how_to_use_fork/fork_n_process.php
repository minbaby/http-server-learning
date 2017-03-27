<?php
// 查看进程数 pstree -apgu `ps -ef | grep minbaby |grep root|grep parent | awk '{print $2}'`
// kill -s USR1 `ps -ef | grep minbaby |grep root|grep parent | awk '{print $2}'`

$signs = [SIGUSR1 => 'SIGUSR1', SIGUSR2 => 'SIGUSR2', SIGCHLD => 'SIGCHLD', SIGALRM => 'SIGALRM'];

function register()
{
    global $signs;
    foreach ($signs as $k => $v) {
        pcntl_signal($k, "sig_handler");
    }
    pcntl_signal_dispatch();
}


function sig_handler($sig)
{
    global $signs;
    echo sprintf("sig-%s-%s", $sig, $signs[$sig]), PHP_EOL;
}

function getProcessInfo($msg, $pid, $ppid)
{
    return sprintf("[minbaby/php] %s-[pid]%s-[ppid]%s\n", $msg, $pid, $ppid);
}

$n = 4;

$pid = pcntl_fork();
if ($pid === -1) {
    // 发生错误了
    echo "e2", PHP_EOL;
    exit(-1);
} elseif ($pid === 0) {
    // 子进程
    @cli_set_process_title(getProcessInfo('root child', posix_getpid(), posix_getppid()));
    while (1) {
        sleep(1);
    }
} else {
    // 父进程1
    @cli_set_process_title(getProcessInfo('root parent', posix_getpid(), posix_getppid()));
    foreach (range(0, $n - 2) as $item) { // 减去 执行 foreach 的这个父进程生成的子进程
        $pid = pcntl_fork();
        if ($pid === -1) {
            // error
            echo "e", PHP_EOL;
        } elseif ($pid === 0) {
            @cli_set_process_title(getProcessInfo('child child', posix_getpid(), posix_getppid()));
            while (1) {
                sleep(1);
            }
            exit(1); // 退出，不在执行之后的 fork 命令
        } else {
            //我是父进程1。。。。
        }
    }

    register();//注册响应事件 子进程不响应事件

    // 假装在做事情，
    while (true) {
        echo __LINE__, PHP_EOL;
        sleep(1000);
        pcntl_signal_dispatch(); // 不知道这到底是杀个意思， 反正需要在做事完成之后执行来触发信号机制
    }

    pcntl_wait($ret);
}
