<?php

namespace Test\Prometheus\Redis;

use Prometheus\Storage\Redis;

trait InitializationTrait
{
    public function configureAdapter()
    {
        $this->adapter = Redis::usingRedis(
            new \Redis(),
            array('host' => REDIS_HOST)
        );
        $this->adapter->flushRedis();
    }
}
