<?php

require __DIR__ . '/../vendor/autoload.php';

use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis;


error_log('c='. $_GET['c']);

$adapter = require __DIR__ . '/initialize.php';
$registry = new CollectorRegistry($adapter);

$gauge = $registry->registerGauge('test', 'some_gauge', 'it sets', ['type']);
$gauge->set($_GET['c'], ['blue']);

echo "OK\n";
