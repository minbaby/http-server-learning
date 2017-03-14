<?php

foreach (glob("*.php") as $item) {
    if ($item == "bootstrap.php") {
        continue;
    }
    include_once $item;
}

$app = new \Minbaby\Application();
$app->run();
