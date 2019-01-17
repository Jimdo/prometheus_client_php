<?php


namespace Test\Prometheus\APCU;

use Prometheus\Storage\APCU;
use Test\Prometheus\AbstractCollectorRegistryTest;

/**
 * @requires extension apcu
 */
class CollectorRegistryTest extends AbstractCollectorRegistryTest
{

    public function configureAdapter()
    {
        $this->adapter = new APCU();
        $this->adapter->flushAPCU()
    }
}
