<?php


namespace Test\Prometheus\Predis;

use Prometheus\Storage\Predis;
use Test\Prometheus\AbstractGaugeTest;

/**
 * See https://prometheus.io/docs/instrumenting/exposition_formats/
 */
class GaugeTest extends AbstractGaugeTest
{

    public function configureAdapter()
    {
        $this->adapter = new Predis(array('host' => REDIS_HOST));
        $this->adapter->flushRedis();
    }
}
