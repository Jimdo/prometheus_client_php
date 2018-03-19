<?php


namespace Test\Prometheus\APCu;

use Prometheus\Storage\APCu;
use Test\Prometheus\AbstractCollectorRegistryTest;

class CollectorRegistryTest extends AbstractCollectorRegistryTest
{
    public function configureAdapter()
    {
        $this->adapter = new APCu();
        $this->adapter->flushAPC();
    }
}
