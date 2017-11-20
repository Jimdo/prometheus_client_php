<?php


namespace Test\Prometheus\APCu;

use Prometheus\Storage\APCu;
use Test\Prometheus\AbstractCollectorRegistryTest;

/**
 * @requires extension apcu
 * @requires function APCUIterator::__construct
 */
class CollectorRegistryTest extends AbstractCollectorRegistryTest
{
    public function configureAdapter()
    {
        $this->adapter = new APCu();
        $this->adapter->flushAPC();
    }
}
