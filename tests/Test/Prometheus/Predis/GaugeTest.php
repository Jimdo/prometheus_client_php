<?php

namespace Test\Prometheus\Predis;

use Test\Prometheus\AbstractGaugeTest;

/**
 * See https://prometheus.io/docs/instrumenting/exposition_formats/
 */
class GaugeTest extends AbstractGaugeTest
{
    use InitializationTrait;
}
