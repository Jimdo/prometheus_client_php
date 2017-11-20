<?php


namespace Test\Prometheus\APCu;

use Prometheus\Storage\APCu;
use Test\Prometheus\AbstractCounterTest;

/**
 * See https://prometheus.io/docs/instrumenting/exposition_formats/
 * @requires extension apcu
 * @requires function APCUIterator::__construct
 */
class CounterTest extends AbstractCounterTest
{
    public function configureAdapter()
    {
        $this->adapter = new APCu();
        $this->adapter->flushAPC();
    }
}
