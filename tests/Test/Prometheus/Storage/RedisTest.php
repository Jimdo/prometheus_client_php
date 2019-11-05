<?php


namespace Prometheus\Storage;

/**
 * @requires extension redis
 */
class RedisTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Prometheus\Exception\StorageException
     * @expectedExceptionMessage Can't connect to Redis server
     */
    public function itShouldThrowAnExceptionOnConnectionFailure()
    {
        $redis = new Redis(array('host' => 'doesntexist.test'));
        $redis->flushRedis();
    }

    public function testReuseRedisClient()
    {
        $redisClient = $this->getMockBuilder(\Redis::class)->getMock();
        $redisStorage = new Redis(['redis' => $redisClient]);

        $this->assertAttributeEquals($redisClient, 'redis', $redisStorage);

        $redisClient->expects($this->atLeastOnce())
            ->method('flushAll');

        $redisClient->expects($this->never())
            ->method('connect');

        $redisStorage->flushRedis();
    }

    public function testReuseRedisClientWithDefaultOptions()
    {
        $redisClient = $this->getMockBuilder(\Redis::class)->getMock();

        Redis::setDefaultOptions(['redis' => $redisClient]);

        $redisStorage = new Redis();

        $this->assertAttributeEquals($redisClient, 'redis', $redisStorage);
    }
}
