<?php


namespace Prometheus;


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;

class PushGateway
{
    /** @var \Psr\Http\Message\UriInterface  */
    private $address;
    /** @var string */
    private $hostPort;

    /**
     * PushGateway constructor.
     * @param $address string host:port of the push gateway
     */
    public function __construct($address)
    {
        $this->address = Uri::fromParts(array_merge(['scheme' => 'http'], parse_url($address)));
        $this->hostPort = $this->address->getHost() . ':' . $this->address->getPort();
    }

    /**
     * Pushes all metrics in a Collector, replacing all those with the same job.
     * Uses HTTP PUT.
     * @param CollectorRegistry $collectorRegistry
     * @param $job
     * @param $groupingKey
     */
    public function push(CollectorRegistry $collectorRegistry, $job, $groupingKey = null)
    {
        $this->doRequest($collectorRegistry, $job, $groupingKey, 'put');
    }

    /**
     * Pushes all metrics in a Collector, replacing only previously pushed metrics of the same name and job.
     * Uses HTTP POST.
     * @param CollectorRegistry $collectorRegistry
     * @param $job
     * @param $groupingKey
     */
    public function pushAdd(CollectorRegistry $collectorRegistry, $job, $groupingKey = null)
    {
        $this->doRequest($collectorRegistry, $job, $groupingKey, 'post');
    }

    /**
     * Deletes metrics from the Pushgateway.
     * Uses HTTP POST.
     * @param $job
     * @param $groupingKey
     */
    public function delete($job, $groupingKey = null)
    {
        $this->doRequest(null, $job, $groupingKey, 'delete');
    }

    /**
     * @param CollectorRegistry $collectorRegistry
     * @param $job
     * @param $groupingKey
     * @param $method
     */
    private function doRequest(CollectorRegistry $collectorRegistry, $job, $groupingKey, $method)
    {
        $path = '/metrics/job/' . $job;
        if (!empty($groupingKey)) {
            foreach ($groupingKey as $label => $value) {
                $path .= '/' . $label . '/' . $value;
            }
        }

        $url = $this->address->withPath($path);

        $client = new Client();
        $requestOptions = [
            'headers' => [
                'Content-Type' => RenderTextFormat::MIME_TYPE,
            ],
            'connect_timeout' => 10,
            'timeout' => 20,
        ];
        if ($method != 'delete') {
            $renderer = new RenderTextFormat();
            $requestOptions['body'] = $renderer->render($collectorRegistry->getMetricFamilySamples());
        }
        $response = $client->request($method, $url, $requestOptions);
        $statusCode = $response->getStatusCode();
        if ($statusCode != 202) {
            $msg = 'Unexpected status code ' . $statusCode . ' received from pushgateway ' . $this->hostPort . ': ' . $response->getBody();
            throw new \RuntimeException($msg);
        }
    }

}
