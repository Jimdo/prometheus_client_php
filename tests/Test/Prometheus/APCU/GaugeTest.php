<?php


namespace Test\Prometheus\APCU;

use Prometheus\Storage\APCU;
use Test\Prometheus\AbstractGaugeTest;

/**
 * See https://prometheus.io/docs/instrumenting/exposition_formats/
 * @requires extension apcu
 */
class GaugeTest extends AbstractGaugeTest
{
    public function configureAdapter()
    {
        $this->adapter = new APCU();
        $this->adapter->flushAPCU();
    }
}
