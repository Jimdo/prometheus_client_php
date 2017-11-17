<?php


namespace Test\Prometheus\APCu;

use Prometheus\Storage\APCu;
use Test\Prometheus\AbstractGaugeTest;

/**
 * See https://prometheus.io/docs/instrumenting/exposition_formats/
 * @requires extension apcu
 * @requires function APCUIterator::__construct
 */
class GaugeTest extends AbstractGaugeTest
{
    public function configureAdapter()
    {
        $this->adapter = new APCu();
        $this->adapter->flushAPC();
    }
}
