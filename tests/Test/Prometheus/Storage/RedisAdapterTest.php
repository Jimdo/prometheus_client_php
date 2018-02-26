<?php


namespace Prometheus\Storage;


class RedisAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Prometheus\Exception\StorageException
     * @expectedExceptionMessage Can't connect to Redis server
     */
    public function itShouldThrowAnExceptionOnConnectionFailure()
    {
        $redis = RedisAdapter::forRedis(new \Redis, ['host' => 'doesntexist.test']);
        $redis->hGetAll('test');
    }

}
