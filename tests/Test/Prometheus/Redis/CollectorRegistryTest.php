<?php


namespace Test\Prometheus\Redis;

use function class_exists;
use Prometheus\CollectorRegistry;
use Prometheus\Counter;
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

        $this->adapter->flushRedis();

        $this->assertEquals('bar', $redis->get('foo'));
        $this->assertEquals([], $redis->sMembers($counterRedisKey));

        $redis->del('foo');
    }
}
