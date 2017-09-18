<?php

namespace Prometheus\Storage;

use Predis\Client;
use Prometheus\Counter;
use Prometheus\Exception\StorageException;
use Prometheus\Gauge;
use Prometheus\Histogram;
use Prometheus\MetricFamilySamples;

class Predis implements Adapter
{
    const PROMETHEUS_METRIC_KEYS_SUFFIX = '_METRIC_KEYS';

    private static $defaultOptions = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'timeout' => 0.1,
        'read_timeout' => 10,
        'persistent_connections' => false,
    ];

    private static $prefix = 'PROMETHEUS_';

    private $options;
    /**
     * @var Client
     */
    private $predis;

    public function __construct(array $options = array())
    {
        $this->options = array_merge(self::$defaultOptions, $options);
    }

    /**
     * @param array $options
     */
    public static function setDefaultOptions(array $options)
    {
        self::$defaultOptions = array_merge(self::$defaultOptions, $options);
    }

    public static function setPrefix($prefix)
    {
        self::$prefix = $prefix;
    }

    public function flushRedis()
    {
        $this->openConnection();
        $this->predis->flushall();
    }

    /**
     * @return MetricFamilySamples[]
     * @throws StorageException
     */
    public function collect()
    {
        $this->openConnection();
        $metrics = $this->collectHistograms();
        $metrics = array_merge($metrics, $this->collectGauges());
        $metrics = array_merge($metrics, $this->collectCounters());
        return array_map(
            function (array $metric) {
                return new MetricFamilySamples($metric);
            },
            $metrics
        );
    }

    /**
     * @throws StorageException
     */
    private function openConnection()
    {
        try {

            $this->predis = new Client([
                'scheme' => 'tcp',
                'host'   => $this->options['host'],
                'port'   => $this->options['port'],
                'timeout' => $this->options['timeout'],
                'read_write_timeout' => $this->options['read_timeout'],
                'persistent' => $this->options['persistent_connections'],
            ]);

        } catch (\RedisException $e) {
            throw new StorageException("Can't connect to Redis server", 0, $e);
        }
    }

    public function updateHistogram(array $data)
    {
        $this->openConnection();
        $bucketToIncrease = '+Inf';
        foreach ($data['buckets'] as $bucket) {
            if ($data['value'] <= $bucket) {
                $bucketToIncrease = $bucket;
                break;
            }
        }
        $metaData = $data;
        unset($metaData['value']);
        unset($metaData['labelValues']);
        $this->predis->eval(<<<LUA
local increment = redis.call('hIncrByFloat', KEYS[1], KEYS[2], ARGV[1])
redis.call('hIncrBy', KEYS[1], KEYS[3], 1)
if increment == ARGV[1] then
    redis.call('hSet', KEYS[1], '__meta', ARGV[2])
    redis.call('sAdd', KEYS[4], KEYS[1])
end
LUA
            ,
            4,
            $this->toMetricKey($data),
            json_encode(array('b' => 'sum', 'labelValues' => $data['labelValues'])),
            json_encode(array('b' => $bucketToIncrease, 'labelValues' => $data['labelValues'])),
            self::$prefix . Histogram::TYPE . self::PROMETHEUS_METRIC_KEYS_SUFFIX,
            $data['value'],
            json_encode($metaData)
        );
    }

    public function updateGauge(array $data)
    {
        $this->openConnection();
        $metaData = $data;
        unset($metaData['value']);
        unset($metaData['labelValues']);
        unset($metaData['command']);
        $this->predis->eval(<<<LUA
local result = redis.call(KEYS[2], KEYS[1], KEYS[4], ARGV[1])

if KEYS[2] == 'hSet' then
    if result == 1 then
        redis.call('hSet', KEYS[1], '__meta', ARGV[2])
        redis.call('sAdd', KEYS[3], KEYS[1])
    end
else
    if result == ARGV[1] then
        redis.call('hSet', KEYS[1], '__meta', ARGV[2])
        redis.call('sAdd', KEYS[3], KEYS[1])
    end
end
LUA
            ,
            4,
            $this->toMetricKey($data),
            $this->getRedisCommand($data['command']),
            self::$prefix . Gauge::TYPE . self::PROMETHEUS_METRIC_KEYS_SUFFIX,
            json_encode($data['labelValues']),
            $data['value'],
            json_encode($metaData)
        );
    }

    public function updateCounter(array $data)
    {
        $this->openConnection();
        $metaData = $data;
        unset($metaData['value']);
        unset($metaData['labelValues']);
        unset($metaData['command']);
        $result = $this->predis->eval(<<<LUA
local result = redis.call(KEYS[2], KEYS[1], KEYS[4], ARGV[1])
if result == tonumber(ARGV[1]) then
    redis.call('hMSet', KEYS[1], '__meta', ARGV[2])
    redis.call('sAdd', KEYS[3], KEYS[1])
end
return result
LUA
            ,
            4,
            $this->toMetricKey($data),
            $this->getRedisCommand($data['command']),
            self::$prefix . Counter::TYPE . self::PROMETHEUS_METRIC_KEYS_SUFFIX,
            json_encode($data['labelValues']),
            $data['value'],
            json_encode($metaData)
        );
        return $result;
    }

    private function collectHistograms()
    {
        $keys = $this->predis->smembers(self::$prefix . Histogram::TYPE . self::PROMETHEUS_METRIC_KEYS_SUFFIX);
        sort($keys);
        $histograms = array();
        foreach ($keys as $key) {
            $raw = $this->predis->hgetall($key);
            $histogram = json_decode($raw['__meta'], true);
            unset($raw['__meta']);
            $histogram['samples'] = array();

            // Add the Inf bucket so we can compute it later on
            $histogram['buckets'][] = '+Inf';

            $allLabelValues = array();
            foreach (array_keys($raw) as $k) {
                $d = json_decode($k, true);
                if ($d['b'] == 'sum') {
                    continue;
                }
                $allLabelValues[] = $d['labelValues'];
            }

            // We need set semantics.
            // This is the equivalent of array_unique but for arrays of arrays.
            $allLabelValues = array_map("unserialize", array_unique(array_map("serialize", $allLabelValues)));
            sort($allLabelValues);

            foreach ($allLabelValues as $labelValues) {
                // Fill up all buckets.
                // If the bucket doesn't exist fill in values from
                // the previous one.
                $acc = 0;
                foreach ($histogram['buckets'] as $bucket) {
                    $bucketKey = json_encode(array('b' => $bucket, 'labelValues' => $labelValues));
                    if (!isset($raw[$bucketKey])) {
                        $histogram['samples'][] = array(
                            'name' => $histogram['name'] . '_bucket',
                            'labelNames' => array('le'),
                            'labelValues' => array_merge($labelValues, array($bucket)),
                            'value' => $acc
                        );
                    } else {
                        $acc += $raw[$bucketKey];
                        $histogram['samples'][] = array(
                            'name' => $histogram['name'] . '_bucket',
                            'labelNames' => array('le'),
                            'labelValues' => array_merge($labelValues, array($bucket)),
                            'value' => $acc
                        );
                    }
                }

                // Add the count
                $histogram['samples'][] = array(
                    'name' => $histogram['name'] . '_count',
                    'labelNames' => array(),
                    'labelValues' => $labelValues,
                    'value' => $acc
                );

                // Add the sum
                $histogram['samples'][] = array(
                    'name' => $histogram['name'] . '_sum',
                    'labelNames' => array(),
                    'labelValues' => $labelValues,
                    'value' => $raw[json_encode(array('b' => 'sum', 'labelValues' => $labelValues))]
                );
            }
            $histograms[] = $histogram;
        }
        return $histograms;
    }

    private function collectGauges()
    {
        $keys = $this->predis->smembers(self::$prefix . Gauge::TYPE . self::PROMETHEUS_METRIC_KEYS_SUFFIX);
        sort($keys);
        $gauges = array();
        foreach ($keys as $key) {
            $raw = $this->predis->hgetall($key);
            $gauge = json_decode($raw['__meta'], true);
            unset($raw['__meta']);
            $gauge['samples'] = array();
            foreach ($raw as $k => $value) {
                $gauge['samples'][] = array(
                    'name' => $gauge['name'],
                    'labelNames' => array(),
                    'labelValues' => json_decode($k, true),
                    'value' => $value
                );
            }
            usort($gauge['samples'], function($a, $b){
                return strcmp(implode("", $a['labelValues']), implode("", $b['labelValues']));
            });
            $gauges[] = $gauge;
        }
        return $gauges;
    }

    private function collectCounters()
    {
        $keys = $this->predis->smembers(self::$prefix . Counter::TYPE . self::PROMETHEUS_METRIC_KEYS_SUFFIX);
        sort($keys);
        $counters = array();
        foreach ($keys as $key) {
            $raw = $this->predis->hgetall($key);
            $counter = json_decode($raw['__meta'], true);
            unset($raw['__meta']);
            $counter['samples'] = array();
            foreach ($raw as $k => $value) {
                $counter['samples'][] = array(
                    'name' => $counter['name'],
                    'labelNames' => array(),
                    'labelValues' => json_decode($k, true),
                    'value' => $value
                );
            }
            usort($counter['samples'], function($a, $b){
                return strcmp(implode("", $a['labelValues']), implode("", $b['labelValues']));
            });
            $counters[] = $counter;
        }
        return $counters;
    }

    private function getRedisCommand($cmd)
    {
        switch ($cmd) {
            case Adapter::COMMAND_INCREMENT_INTEGER:
                return 'hIncrBy';
            case Adapter::COMMAND_INCREMENT_FLOAT:
                return 'hIncrByFloat';
            case Adapter::COMMAND_SET:
                return 'hSet';
            default:
                throw new \InvalidArgumentException("Unknown command");
        }
    }

    /**
     * @param array $data
     * @return string
     */
    private function toMetricKey(array $data)
    {
        return implode(':', array(self::$prefix, $data['type'], $data['name']));
    }
}
