<?php


namespace Test\Prometheus\Predis;

use Prometheus\Storage\Predis;
use Test\Prometheus\AbstractCollectorRegistryTest;

class CollectorRegistryTest extends AbstractCollectorRegistryTest
{
    public function configureAdapter()
    {
        $this->adapter = new Predis(new \Predis\Client(['host' => REDIS_HOST]));
        $this->adapter->flushRedis();
    }
}
