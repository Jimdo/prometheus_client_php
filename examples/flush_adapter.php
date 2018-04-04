<?php
require __DIR__ . '/../vendor/autoload.php';

$adapter = $_GET['adapter'];

if ($adapter === 'redis') {
    define('REDIS_HOST', isset($_SERVER['REDIS_HOST']) ? $_SERVER['REDIS_HOST'] : '127.0.0.1');

    $adapter = new Prometheus\Storage\Redis(array('host' => REDIS_HOST));
} elseif ($adapter === 'apc') {
    $adapter = new Prometheus\Storage\APC();
} elseif ($adapter === 'in-memory') {
    $adapter = new Prometheus\Storage\InMemory();
}

$adapter->flush();
