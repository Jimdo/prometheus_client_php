<?php

require __DIR__ . '/../vendor/autoload.php';

use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\Redis;

$adapter = require __DIR__ . '/initialize.php';

$registry = new CollectorRegistry($adapter);
$renderer = new RenderTextFormat();
$result = $renderer->render($registry->getMetricFamilySamples());

header('Content-type: ' . RenderTextFormat::MIME_TYPE);
echo $result;
