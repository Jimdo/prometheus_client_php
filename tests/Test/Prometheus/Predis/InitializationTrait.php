<?php

namespace Test\Prometheus\Predis;

use Predis\Client;
use Prometheus\Storage\Redis;

trait InitializationTrait
{
    public function configureAdapter()
    {
        $this->adapter = Redis::usingPredis(
            new Client(['host' => REDIS_HOST])
        );
        $this->adapter->flushRedis();
    }
}
