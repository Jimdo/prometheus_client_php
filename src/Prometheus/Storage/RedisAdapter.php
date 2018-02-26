<?php

namespace Prometheus\Storage;

use Predis\Client;
use Prometheus\Exception\StorageException;

class RedisAdapter
{
    /**
     * @var \Redis|Client
     */
    private $redis;

    private $options;

    /**
     * @param \Redis $redis
     * @param array $options
     * @return static
     */
    public static function forRedis(\Redis $redis, array $options = [])
    {
        $adapter = new self($redis);
        $adapter->options = array_replace([
            'host' => '127.0.0.1',
            'port' => 6379,
            'timeout' => 0.1,
            'read_timeout' => 10,
            'persistent_connections' => false,
            'password' => null,
        ], $options);

        return $adapter;
    }

    /**
     * @param Client $client
     * @return RedisAdapter
     */
    public static function forPredis(Client $client)
    {
        return new self($client);
    }

    /**
     * @param \Redis|Client $redisInstance
     */
    private function __construct($redisInstance)
    {
        $this->redis = $redisInstance;
    }

    /**
     * @param string $script
     * @param array $args
     * @param int $numKeys
     */
    public function evaluate($script, $args = array(), $numKeys = 0)
    {
        if ($this->redis instanceof Client) {
            $this->redis->eval($script, $numKeys, ...$args);
            return;
        }

        $this->openConnection();
        $this->redis->eval($script, $args, $numKeys);
    }

    /**
     * @param string $key
     * @return array
     */
    public function sMembers($key)
    {
        $this->openConnection();
        return $this->redis->smembers($key);
    }

    /**
     * @param string $key
     * @return array
     */
    public function hGetAll($key)
    {
        $this->openConnection();
        return $this->redis->hgetall($key);
    }

    public function flushAll()
    {
        $this->openConnection();
        $this->redis->flushall();
    }

    /**
     * @throws StorageException
     */
    private function openConnection()
    {
        if ($this->redis instanceof Client) {
            return;
        }

        try {
            if ($this->options['persistent_connections']) {
                @$this->redis->pconnect($this->options['host'], $this->options['port'], $this->options['timeout']);
            } else {
                @$this->redis->connect($this->options['host'], $this->options['port'], $this->options['timeout']);
            }
            if ($this->options['password']) {
                $this->redis->auth($this->options['password']);
            }
            $this->redis->setOption(\Redis::OPT_READ_TIMEOUT, $this->options['read_timeout']);
        } catch (\RedisException $e) {
            throw new StorageException("Can't connect to Redis server", 0, $e);
        }
    }
}