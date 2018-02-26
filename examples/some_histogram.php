<?php

require __DIR__ . '/../vendor/autoload.php';

use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis;

error_log('c='. $_GET['c']);

$adapter = require __DIR__ . '/initialize.php';
$registry = new CollectorRegistry($adapter);

$histogram = $registry->registerHistogram('test', 'some_histogram', 'it observes', ['type'], [0.1, 1, 2, 3.5, 4, 5, 6, 7, 8, 9]);
$histogram->observe($_GET['c'], ['blue']);

echo "OK\n";
