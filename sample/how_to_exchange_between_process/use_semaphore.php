<?php

// http://php.net/manual/zh/book.sem.php
// 信号量(用作锁的实现，类似 mutex 功能) + 共享内存 + IPC
// Remember, that shared memory is NOT safe against simultaneous access. Use semaphores for synchronization.
// 共享内存在同步访问中也不是安全的。同步操作使用信号量。
// sem_ ==> semaphores
// shm_ ==> shared memory

// 参考：http://php.net/manual/zh/ref.sem.php#22553


$MEM_SIZE = 512;

$SEM_KEY = 1;

$SHM_KEY = 2;


echo "Start.\n";
// Get semaphore
$sem_id = sem_get($SEM_KEY, 1);
if ($sem_id === false) {
    echo "Fail to get semaphore";
    exit;
} else {
    echo "Got semaphore $sem_id.\n";
}

// Accuire semaphore
if (!sem_acquire($sem_id)) { // 申请
    echo "Fail to aquire semaphore $sem_id.\n";
    sem_remove($sem_id);
    exit;
} else {
    echo "Success aquire semaphore $sem_id.\n";
}

$shm_id = shm_attach($SHM_KEY, $MEM_SIZE);
if ($shm_id === false) {
    echo "Fail to attach shared memory.\n";
    sem_remove($sem_id);
    exit;
} else {
    echo "Success to attach shared memory : $shm_id.\n";
}

// Write variable 1
if (!shm_put_var($shm_id, 1, "Variable 1")) {
    echo "Fail to put var 1 on shared memory $shm_id.\n";
    sem_remove($sem_id);
    shm_remove($shm_id);
    exit;
} else {
    echo "Write var1 to shared memory.\n";
}

// Write variable 2
if (!shm_put_var($shm_id, 2, "Variable 2")) {
    echo "Fail to put var 2 on shared memory $shm_id.\n";
    sem_remove($sem_id);
    shm_remove($shm_id);
    exit;
} else {
    echo "Write var2 to shared memory.\n";
}

// Read variable 1
$var1 = shm_get_var($shm_id, 1);
if ($var1 === false) {
    echo "Fail to retrive Var 1 from Shared memory $shm_id, return value=$var1.\n";
} else {
    echo "Read var1=$var1.\n";
}

// Read variable 1
$var2 = shm_get_var($shm_id, 2);
if ($var1 === false) {
    echo "Fail to retrive Var 2 from Shared memory $shm_id, return value=$var2.\n";
} else {
    echo "Read var2=$var2.\n";
}

// Release semaphore
if (!sem_release($sem_id)) {
    echo "Fail to release $sem_id semaphore.\n";
} else {
    echo "Semaphore $sem_id released.\n";
}

// remove shared memory segmant from SysV
if (shm_remove($shm_id)) {
    echo "Shared memory successfully removed from SysV.\n";
} else {
    echo "Fail to remove $shm_id shared memory from SysV.\n";
}

// Remove semaphore
if (sem_remove($sem_id)) {
    echo "semaphore removed successfully from SysV.\n";
} else {
    echo "Fail to remove $sem_id semaphore from SysV.\n";
}
echo "End.\n";


echo "============================================================\n";

echo "use sem[semaphore]\n";

$proj = "M";

$key = ftok('/tmp/a.log', $proj); // >> touch /tmp/a.log

$max_acquire = 10; // 最大申请次数 10 次
echo "only acquire 10\n";
$sem_id = sem_get(1, $max_acquire);

echo "[start acquire...]\n";
foreach (range(0, 10) as $item) {
    $flag = sem_acquire($sem_id, true); // 默认是阻塞的， 我们设置为非阻塞。
    if ($flag == true) {
        echo "acquire success:" . $item . PHP_EOL;
    } else {
        echo "acquire failed:" . $item . PHP_EOL;
    }
    usleep(1000 * 100);
}

echo "[start release...]\n";
foreach (range(0, 10) as $item) {
    $flag = @sem_release($sem_id);  // 释放失败，会有个 warning
    if ($flag == true) {
        echo "release success:" . $item . PHP_EOL;
    } else {
        echo "release failed:" . $item . PHP_EOL;
    }
}


$flag = sem_remove($sem_id); //删掉
if ($flag == true) {
    echo "remove success\n";
} else {
    echo "remove error\n";
}

echo "============================================================\n";

echo "use shm[shared memory]\n";

$proj = "M";
$size = 1024 * 1024; //单位 bytes

$key = ftok('/tmp/a.log', $proj); // >> touch /tmp/a.log

$shm_id = shm_attach($key, $size);

$var_key = 1122;

if (shm_has_var($shm_id, $var_key)) {
    echo "has {$var_key}\n";
} else {
    echo "not has {$var_key}\n";
}

$flag = shm_put_var($shm_id, $var_key, $_SERVER);
if ($flag == true) {
    echo "shm_put_var success\n";
} else {
    echo "shm_put_var error\n";
}

if (shm_has_var($shm_id, $var_key)) {
    echo "has {$var_key}\n";
} else {
    echo "not has {$var_key}\n";
}

echo json_encode(shm_get_var($shm_id, $var_key)), PHP_EOL;

if (shm_remove_var($shm_id, $var_key)) {
    echo "remove success {$var_key}\n";
} else {
    echo "remove failed {$var_key}\n";
}

if (shm_has_var($shm_id, $var_key)) {
    echo "has {$var_key}\n";
} else {
    echo "not has {$var_key}\n";
}

$flag = shm_remove($shm_id);
if ($flag == true) {
    echo "shm_remove success\n";
} else {
    echo "shm_remove error\n";
}


$flag = shm_detach($shm_id); //Remember, that shared memory still exist in the Unix system and the data is still present
if ($flag == true) {
    echo "detach success\n";
} else {
    echo "detach error\n";
}

echo "============================================================\n";

echo "use msg[message queue]\n";

$proj = "M";

$key = ftok('/tmp/a.log', $proj); // >> touch /tmp/a.log

$msg_id = msg_get_queue($key);
if (msg_queue_exists($key)) {
    echo "msg_queue_exists\n";
} else {
    echo "msg_queue_not_exists\n";
}

$config = msg_stat_queue($msg_id);
echo json_encode($config), PHP_EOL;

// send to message queue
$msgType = 1;
$message = ['a' => time(), 'b' => rand(0, 1000)];
$serialize = true;
$blocking = true;
$flag =  msg_send($msg_id, $msgType, json_encode($message), $serialize, $blocking, $errorCode);
if ($flag) {
    echo "msg_send success \n";
} else {
    echo "msg_send failed : {$errorCode}\n";
}

// receive message
$flag =  msg_receive($msg_id, 0, $msgType1, $config['msg_qbytes'], $message, true, 0, $error);
if ($flag) {
    echo "msg_receive success \n";
    echo "msg:{$message}, len:{$msgType1}";
} else {
    echo "msg_receive failed: {$error}\n";
}


if (msg_remove_queue($msg_id)) {
    echo "msg_remove_queue success \n";
} else {
    echo "msg_remove_queue failed\n";
}
