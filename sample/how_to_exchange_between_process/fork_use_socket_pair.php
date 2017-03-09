<?php

// 向父进程发送 alrm 信号， 然后父进程通过 socket，发送到子进程。

function sig_handler($sig)
{
    global $signs;
    echo sprintf("sig-%s-%s", $sig, $signs[$sig]), PHP_EOL;
}

$sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);

if ($sockets === false) {
    echo "stream_socket_pair create error";
    die(-1);
} else {
    list($socketParent, $socketChild) = $sockets;
    unset($sockets);
}

$pid = pcntl_fork();
if ($pid === 0) {
    echo "子", "-pid-", posix_getpid(), PHP_EOL;

    @cli_set_process_title("php-fork-children"); // osx 不支持

    fclose($socketParent);
    while (true) {
        $str = stream_socket_recvfrom($socketChild, 1024);
        echo "child:[recv-from-parent]", $str, PHP_EOL;
    }
} elseif ($pid === -1) {
    echo 'ERROR', pcntl_get_last_error();
} else {
    fclose($socketChild);
    @cli_set_process_title("php-fork-parent"); // osx 不支持
    echo "父", "-pid-", posix_getpid(), PHP_EOL;
    $status = "";

    while (true) {
        $old = [];
        pcntl_sigprocmask(SIG_BLOCK, [SIGALRM], $old); // 阻塞 ALRM, 直到收到信号位置

        $info = [];
        pcntl_sigwaitinfo([SIGALRM], $info);

        $str = "我收到了 alrm 信号";
        stream_socket_sendto($socketParent, $str, strlen($str));

        pcntl_sigprocmask(SIG_UNBLOCK, [SIGALRM]); // 解除阻塞
    }

    echo "wait...", PHP_EOL;
    pcntl_wait($status); // 需要等待子进程， 防止子进程变成僵尸进程。
    exit;
}
