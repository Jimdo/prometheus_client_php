<?php

namespace Prometheus;

use Prometheus\Exception\MetricNotFoundException;
use Prometheus\Exception\MetricsRegistrationException;

interface RegistryInterface
{
    /**
     * @return MetricFamilySamples[]
     */
    public function getMetricFamilySamples();

    /**
     * @param string $namespace e.g. cms
     * @param string $name e.g. duration_seconds
     * @param string $help e.g. The duration something took in seconds.
     * @param array  $labels e.g. ['controller', 'action']
     *
     * @return Gauge
     * @throws MetricsRegistrationException
     */
    public function registerGauge($namespace, $name, $help, $labels = []);

    /**
     * @param string $namespace
     * @param string $name
     *
     * @return Gauge
     * @throws MetricNotFoundException
     */
    public function getGauge($namespace, $name);

    /**
     * @param string $namespace e.g. cms
     * @param string $name e.g. duration_seconds
     * @param string $help e.g. The duration something took in seconds.
     * @param array  $labels e.g. ['controller', 'action']
     *
     * @return Gauge
     */
    public function getOrRegisterGauge($namespace, $name, $help, $labels = []);

    /**
     * @param string $namespace e.g. cms
     * @param string $name e.g. requests
     * @param string $help e.g. The number of requests made.
     * @param array  $labels e.g. ['controller', 'action']
     *
     * @return Counter
     * @throws MetricsRegistrationException
     */
    public function registerCounter($namespace, $name, $help, $labels = []);

    /**
     * @param string $namespace
     * @param string $name
     *
     * @return Counter
     * @throws MetricNotFoundException
     */
    public function getCounter($namespace, $name);

    /**
     * @param string $namespace e.g. cms
     * @param string $name e.g. requests
     * @param string $help e.g. The number of requests made.
     * @param array  $labels e.g. ['controller', 'action']
     *
     * @return Counter
     */
    public function getOrRegisterCounter($namespace, $name, $help, $labels = []);

    /**
     * @param string $namespace e.g. cms
     * @param string $name e.g. duration_seconds
     * @param string $help e.g. A histogram of the duration in seconds.
     * @param array  $labels e.g. ['controller', 'action']
     * @param array  $buckets e.g. [100, 200, 300]
     *
     * @return Histogram
     * @throws MetricsRegistrationException
     */
    public function registerHistogram($namespace, $name, $help, $labels = [], $buckets = null);

    /**
     * @param string $namespace
     * @param string $name
     *
     * @return Histogram
     * @throws MetricNotFoundException
     */
    public function getHistogram($namespace, $name);

    /**
     * @param string $namespace e.g. cms
     * @param string $name e.g. duration_seconds
     * @param string $help e.g. A histogram of the duration in seconds.
     * @param array  $labels e.g. ['controller', 'action']
     * @param array  $buckets e.g. [100, 200, 300]
     *
     * @return Histogram
     */
    public function getOrRegisterHistogram($namespace, $name, $help, $labels = [], $buckets = null);
}
