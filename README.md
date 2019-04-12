# A prometheus client library written in PHP

[![Build Status](https://travis-ci.org/thebeatapp/prometheus_client_php.svg?branch=master)](https://travis-ci.org/thebeatapp/prometheus_client_php)

This library uses Redis or APCu to do the client side aggregation.
If using Redis, we recommend to run a local Redis instance next to your PHP workers.

## How does it work?

Usually PHP worker processes don't share any state.
You can pick from three adapters.
Redis, APC or an in memory adapter.
While the first needs a separate binary running, the second just needs the [APC](https://pecl.php.net/package/APCU) extension to be installed. If you don't need persistent metrics between requests (e.g. a long running cron job or script) the in memory adapter might be suitable to use.

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

You can now add default metrics (e.g. environment variables).

This can be especially useful in situations like CLI applications
with APC adapter, that does not share the same memory for fpm and 
all the other CLI processes.

In this case the use of push gateway comes in handy, but makes it hard to pass metrics that remain static. 

`applyDefaultLabels` aims to bridge this gap, 
by giving you the ability to add default configuration:

```php
$registry = new CollectorRegistry(new InMemory());

$registry->applyDefaultLabels(['host' => $_SERVER['SERVER_NAME']]);
```

Also look at the [examples](examples).

## Development

### Dependencies

* PHP 5.6
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
