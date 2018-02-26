<?php

namespace Test\Prometheus\Redis;

use Test\Prometheus\AbstractCounterTest;

/**
 * See https://prometheus.io/docs/instrumenting/exposition_formats/
 */
class CounterTest extends AbstractCounterTest
{
    use InitializationTrait;
}
