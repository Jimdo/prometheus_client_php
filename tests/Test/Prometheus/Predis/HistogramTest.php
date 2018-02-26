<?php

namespace Test\Prometheus\Predis;

use Test\Prometheus\AbstractHistogramTest;

/**
 * See https://prometheus.io/docs/instrumenting/exposition_formats/
 */
class HistogramTest extends AbstractHistogramTest
{
    use InitializationTrait;
}
