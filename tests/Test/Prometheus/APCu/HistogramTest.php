<?php


namespace Test\Prometheus\APCu;

use Prometheus\Storage\APCu;
use Test\Prometheus\AbstractHistogramTest;

/**
 * See https://prometheus.io/docs/instrumenting/exposition_formats/
 * @requires extension apcu
 * @requires function APCUIterator::__construct
 */
class HistogramTest extends AbstractHistogramTest
{
    public function configureAdapter()
    {
        $this->adapter = new APCu();
        $this->adapter->flushAPC();
    }
}

