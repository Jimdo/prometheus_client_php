<?php


namespace Test\Prometheus\Predis;

use Prometheus\Storage\Predis;
use Test\Prometheus\AbstractCounterTest;

/**
 * See https://prometheus.io/docs/instrumenting/exposition_formats/
 */
class CounterTest extends AbstractCounterTest
{

    public function configureAdapter()
    {
        $this->adapter = new Predis(array('host' => REDIS_HOST));
        $this->adapter->flushRedis();
    }
}
