<?php

namespace Test\Prometheus\Predis;

use Test\Prometheus\AbstractCounterTest;

/**
 * See https://prometheus.io/docs/instrumenting/exposition_formats/
 */
class CounterTest extends AbstractCounterTest
{
    use InitializationTrait;
}
