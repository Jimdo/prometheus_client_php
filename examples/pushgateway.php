<?php
require __DIR__ . '/../vendor/autoload.php';

use Prometheus\Storage\Redis;
use Prometheus\CollectorRegistry;

$adapter = require __DIR__ . '/initialize.php';

$registry = new CollectorRegistry($adapter);

$counter = $registry->registerCounter('test', 'some_counter', 'it increases', ['type']);
$counter->incBy(6, ['blue']);

$pushGateway = new \Prometheus\PushGateway('192.168.59.100:9091');
$pushGateway->push($registry, 'my_job', array('instance'=>'foo'));
