<?php

declare(ticks = 1);

$max=5;
$child=0;

// function for signal handler
function sig_handler($signo) {
    global $child;
    switch ($signo) {
        case SIGCHLD:
            echo "SIGCHLD received\n";
            $child--;
    }
}

// install signal handler for dead kids
pcntl_signal(SIGCHLD, "sig_handler");

while (1){
    $child++;
    $pid=pcntl_fork();

    if ($pid == -1) {
        die("could not fork");
    } else if ($pid) {

        // we are the parent
        if ( $child >= $max ){
            pcntl_wait($status);
            $child++;
        }
    } else {
        // we are the child
        echo "\t Starting new child | now we de have $child child processes\n";
        // presumably doing something interesting
        sleep(rand(3,5));
        exit;
    }
}
?>