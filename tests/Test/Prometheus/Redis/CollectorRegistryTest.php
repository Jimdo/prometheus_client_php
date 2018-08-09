<?php


namespace Test\Prometheus\Redis;

use function class_exists;
use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Gauge;
use Prometheus\Histogram;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\Redis;
use Test\Prometheus\AbstractCollectorRegistryTest;

/**
 * @requires extension redis
 */
class CollectorRegistryTest extends AbstractCollectorRegistryTest
{
    public function configureAdapter()
    {
        $this->adapter = new Redis(array('host' => REDIS_HOST));
        $this->adapter->flushRedis();
    }

    /**
     * @test
     */
    public function itShouldOnlyFlushMetricData()
    {
        $redis = new \Redis();
        $redis->connect(REDIS_HOST);
        $redis->set('foo', 'bar');

        $registry = new CollectorRegistry($this->adapter);

        $counter = $registry->registerCounter('test', 'some_counter', 'it increases', ['type']);
        $counter->incBy(6, ['blue']);
        $counterRedisKey = 'PROMETHEUS_' . Counter::TYPE . Redis::PROMETHEUS_METRIC_KEYS_SUFFIX;
        $this->assertEquals(['PROMETHEUS_:counter:test_some_counter'], $redis->sMembers($counterRedisKey));

        $gauge = $registry->registerGauge('test', 'some_gauge', 'this is for testing', array('foo'));
        $gauge->set(35, array('bar'));
        $gaugeRedisKey = 'PROMETHEUS_' . Gauge::TYPE . Redis::PROMETHEUS_METRIC_KEYS_SUFFIX;
        $this->assertEquals(['PROMETHEUS_:gauge:test_some_gauge'], $redis->sMembers($gaugeRedisKey));

        $histogram = $registry->registerHistogram('test', 'some_histogram', 'this is for testing', array('foo', 'bar'), array(0.1, 1, 5, 10));
        $histogram->observe(2, array('cat', 'meow'));
        $histogramRedisKey = 'PROMETHEUS_' . Histogram::TYPE . Redis::PROMETHEUS_METRIC_KEYS_SUFFIX;
        $this->assertEquals(['PROMETHEUS_:histogram:test_some_histogram'], $redis->sMembers($histogramRedisKey));

        $this->adapter->flushRedis();

        $this->assertEquals('bar', $redis->get('foo'));

        $this->assertEquals([], $redis->sMembers($counterRedisKey));
        $this->assertFalse($redis->get('PROMETHEUS_:counter:test_some_counter'));
        $this->assertEquals([], $redis->sMembers($gaugeRedisKey));
        $this->assertFalse($redis->get('PROMETHEUS_:gauge:test_some_gauge'));
        $this->assertEquals([], $redis->sMembers($histogramRedisKey));
        $this->assertFalse($redis->get('PROMETHEUS_:histogram:test_some_histogram'));

        $this->assertEquals("\n", (new RenderTextFormat())->render($registry->getMetricFamilySamples()));

        $redis->del('foo');
    }
}
