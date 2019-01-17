<?php


namespace Test\Prometheus\APCU;

use Prometheus\Storage\APCU;
use Test\Prometheus\AbstractCounterTest;

/**
 * See https://prometheus.io/docs/instrumenting/exposition_formats/
 * @requires extension apcu
 */
class CounterTest extends AbstractCounterTest
{
    public function configureAdapter()
    {
        $this->adapter = new APCU();
        $this->adapter->flushAPCU();
    }
}
