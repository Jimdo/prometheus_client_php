<?php

$adapterName = $_GET['adapter'];

switch($adapterName) {
    case "redis":
        return \Prometheus\Storage\Redis::usingRedis(
            new \Redis(),
            array('host' => isset($_SERVER['REDIS_HOST']) ? $_SERVER['REDIS_HOST'] : '127.0.0.1')
        );
    case "predis":
        return  Prometheus\Storage\Redis::usingPredis(
            new \Predis\Client(['host' => isset($_SERVER['REDIS_HOST']) ? $_SERVER['REDIS_HOST'] : '127.0.0.1'])
        );
    case "apc":
        return new Prometheus\Storage\APC();
    case "in-memory":
        return new Prometheus\Storage\InMemory();
    default:
        throw new \RuntimeException(sprintf('Adapter "" is not supported', $adapterName));
}
