<?php

namespace Test\Prometheus\APCu;

use Prometheus\Storage\APCu;
use RuntimeException;

final class APCuTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        if (extension_loaded('apcu')) {
            $this->markTestSkipped('apcu extension already available');
            return;
        }

        if (class_exists(APCUIterator::class)) {
            $this->markTestSkipped('apcu extension is expected version');
            return;
        }

    }

    /**
     * @test
     */
    public function itShouldThrowAnExceptionWhenAPCUExtensionIsNotLoaded()
    {
        $this->setExpectedException(RuntimeException::class);
        $adapter = new APCu();
    }

}
