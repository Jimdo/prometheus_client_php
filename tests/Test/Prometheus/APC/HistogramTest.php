<?php


namespace Test\Prometheus\APC;

use Prometheus\Storage\APC;
use Test\Prometheus\AbstractHistogramTest;

/**
 * See https://prometheus.io/docs/instrumenting/exposition_formats/
 */
class HistogramTest extends AbstractHistogramTest
{

    public function configureAdapter()
    {
        if(function_exists('apcu_fetch'))
            $this->adapter = new APCU();
        else
            $this->adapter = new APC();
        $this->adapter->flushAPC();
    }
}

