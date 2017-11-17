<?php
declare(strict_types=1);

namespace Prometheus;

interface RendererInterface
{
    /**
     * @param MetricFamilySamples[] $metrics
     *
     * @return string
     */
    public function render(array $metrics);
}
