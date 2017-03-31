#monitor the memory

```php
<?php

MemoryMonitor::init();
$c = [];
while (true) {
    MemoryMonitor::prettyPrint();

    foreach (range(0, 100) as $item) {
        $c[] = $a = new stdClass();
    }

    if (count($c) > 500) {
        $c = [];
    }

    usleep(1000 * 100);
}

```