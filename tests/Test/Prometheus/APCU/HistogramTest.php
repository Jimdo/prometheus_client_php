<?php


namespace Test\Prometheus\APCU;

use Prometheus\Storage\APCU;
use Test\Prometheus\AbstractHistogramTest;

/**
 * See https://prometheus.io/docs/instrumenting/exposition_formats/
 * @requires extension apcu
 */
class HistogramTest extends AbstractHistogramTest
{
    public function configureAdapter()
    {
        $this->adapter = new APCU();
        $this->adapter->flushAPCU();
    }
}

