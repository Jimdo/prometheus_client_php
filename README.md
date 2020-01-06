# :exclamation: New project maintainers :exclamation:

This project is no longer maintained here. Please go to https://github.com/endclothing/prometheus_client_php.

# A prometheus client library written in PHP

[![CircleCI](https://circleci.com/gh/endclothing/prometheus_client_php/tree/master.svg?style=shield)](https://circleci.com/gh/endclothing/prometheus_client_php/tree/master)

This library uses Redis or APCu to do the client side aggregation.
If using Redis, we recommend to run a local Redis instance next to your PHP workers.

## How does it work?

Usually PHP worker processes don't share any state.
You can pick from three adapters.
Redis, APC or an in memory adapter.
While the first needs a separate binary running, the second just needs the [APC](https://pecl.php.net/package/APCU) extension to be installed. If you don't need persistent metrics between requests (e.g. a long running cron job or script) the in memory adapter might be suitable to use.

## Installation

Add as [Composer](https://getcomposer.org/) dependency:

```sh
composer require endclothing/prometheus_client_php
```

## Usage

A simple counter:
```php
\Prometheus\CollectorRegistry::getDefault()
    ->getOrRegisterCounter('', 'some_quick_counter', 'just a quick measurement')
    ->inc();
```

Write some enhanced metrics:
```php
$registry = \Prometheus\CollectorRegistry::getDefault();

$counter = $registry->getOrRegisterCounter('test', 'some_counter', 'it increases', ['type']);
$counter->incBy(3, ['blue']);

$gauge = $registry->getOrRegisterGauge('test', 'some_gauge', 'it sets', ['type']);
$gauge->set(2.5, ['blue']);

$histogram = $registry->getOrRegisterHistogram('test', 'some_histogram', 'it observes', ['type'], [0.1, 1, 2, 3.5, 4, 5, 6, 7, 8, 9]);
$histogram->observe(3.5, ['blue']);
```

Manually register and retrieve metrics (these steps are combined in the `getOrRegister...` methods):
```php
$registry = \Prometheus\CollectorRegistry::getDefault();

$counterA = $registry->registerCounter('test', 'some_counter', 'it increases', ['type']);
$counterA->incBy(3, ['blue']);

// once a metric is registered, it can be retrieved using e.g. getCounter:
$counterB = $registry->getCounter('test', 'some_counter')
$counterB->incBy(2, ['red']);
```

Expose the metrics:
```php
$registry = \Prometheus\CollectorRegistry::getDefault();

$renderer = new RenderTextFormat();
$result = $renderer->render($registry->getMetricFamilySamples());

header('Content-type: ' . RenderTextFormat::MIME_TYPE);
echo $result;
```

Change the Redis options (the example shows the defaults):
```php
\Prometheus\Storage\Redis::setDefaultOptions(
    [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => null,
        'timeout' => 0.1, // in seconds
        'read_timeout' => 10, // in seconds
        'persistent_connections' => false
    ]
);
```

Using the InMemory storage:
```php
$registry = new CollectorRegistry(new InMemory());

$counter = $registry->registerCounter('test', 'some_counter', 'it increases', ['type']);
$counter->incBy(3, ['blue']);

$renderer = new RenderTextFormat();
$result = $renderer->render($registry->getMetricFamilySamples());
```

### Advanced Usage

#### Advanced Histogram Usage
On passing an empty array for the bucket parameter on instantiation, a set of default buckets will be used instead.
Whilst this is a good base for a typical web application, there is named constructor to assist in the generation of
exponential / geometric buckets.

Eg:
```
Histogram::exponentialBuckets(0.05, 1.5, 10);
```

This will start your buckets with a value of 1.5, grow them by a factor of 1.5 per bucket across a set of 10 buckets.

Also look at the [examples](examples).

## Development

### Dependencies

* PHP ^7.3
* PHP Redis extension
* PHP APCu extension
* [Composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)
* Redis

Start a Redis instance:
```
docker-compose up Redis
```

Run the tests:
```
composer install

# when Redis is not listening on localhost:
# export REDIS_HOST=192.168.59.100
./vendor/bin/phpunit
```

## Black box testing

Just start the nginx, fpm & Redis setup with docker-compose:
```
docker-compose up
```
Pick the adapter you want to test.

```
docker-compose run phpunit env ADAPTER=apc vendor/bin/phpunit tests/Test/
docker-compose run phpunit env ADAPTER=redis vendor/bin/phpunit tests/Test/
```
