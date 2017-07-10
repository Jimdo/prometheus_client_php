<?php


namespace Test\Prometheus\APC;

use Prometheus\Storage\APC;
use Prometheus\Storage\APCU;
use Test\Prometheus\AbstractCollectorRegistryTest;

class CollectorRegistryTest extends AbstractCollectorRegistryTest
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
