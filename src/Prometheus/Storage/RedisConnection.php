<?php


namespace Prometheus\Storage;


use Prometheus\Exception\StorageException;

class RedisConnection
{
    const MAX_RETRY_ATTEMPTS = 3;

    private $options;
    private $redis;
    private $connected = false;

    public function __construct($options) {
        $this->options = $options;
    }

    /**
     * @throws StorageException
     */
    public function openConnection($attempt = 0)
    {
        if ($this->connected === true) {
            return;
        }

        if (is_null($this->redis)) {
            $this->redis = new \Redis();
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
            if (isset($this->options['database'])) {
                $this->redis->select($this->options['database']);
            }

            $this->redis->setOption(\Redis::OPT_READ_TIMEOUT, $this->options['read_timeout']);

            $this->connected = true;
        } catch (\RedisException $e) {
            $this->connected = false;
            if ($this->connected = false && $attempt >= self::MAX_RETRY_ATTEMPTS) {
                throw new StorageException("Can't connect to Redis server", 0, $e);
            }
            $this->openConnection($attempt + 1);
        }
    }

    public function __call($name, $arguments)
    {
        return $this->handleConnection(function () use ($name, $arguments) {
            if ($this->connected === false) {
                $this->openConnection();
            }

            return $this->redis->{$name}(...$arguments);
        });
    }

    /**
     * @param $closure
     * @param int $attempt
     * @throws StorageException
     */
    private function handleConnection($closure, $attempt = 0)
    {
        try {
            $this->openConnection();

            if ($this->connected = false && $attempt >= self::MAX_RETRY_ATTEMPTS) {
               throw new \RedisException("Max retries reached");
            }

            return $closure();
        } catch (\RedisException $e) {
            if ($this->connected == false && $attempt >= self::MAX_RETRY_ATTEMPTS) {
                throw new StorageException("Can't connect to Redis server", 0, $e);
            }

            $this->connected = false;
            $this->openConnection();
            $this->handleConnection($closure, $attempt + 1);
        }
    }
}