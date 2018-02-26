<?php
require __DIR__ . '/../vendor/autoload.php';

$adapter = require __DIR__ . '/initialize.php';

if ($adapter instanceof \Prometheus\Storage\Redis) {
    $adapter->flushRedis();
} elseif ($adapter instanceof \Prometheus\Storage\APC) {
    $adapter->flushAPC();
} elseif ($adapter instanceof \Prometheus\Storage\InMemory) {
    $adapter->flushMemory();
}