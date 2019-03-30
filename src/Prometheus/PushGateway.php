<?php


namespace Prometheus;


use GuzzleHttp\Client;

class PushGateway
{
    /**
     * @var Client $client
     */
    private $client;

    /**
     * PushGateway constructor.
     * @param Client $client client with configured push gateway base_uri param, example uri: http://pushgateway.com/metrics/job/
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Pushes all metrics in a Collector, replacing all those with the same job.
     * Uses HTTP PUT.
     * @param CollectorRegistry $collectorRegistry
     * @param string $job
     * @param array|null $groupingKey
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function push(CollectorRegistry $collectorRegistry, $job, $groupingKey = null)
    {
        $this->doRequest('put', $job, $groupingKey, $collectorRegistry);
    }

    /**
     * Pushes all metrics in a Collector, replacing only previously pushed metrics of the same name and job.
     * Uses HTTP POST.
     * @param CollectorRegistry $collectorRegistry
     * @param string $job
     * @param array|null $groupingKey
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pushAdd(CollectorRegistry $collectorRegistry, $job, $groupingKey = null)
    {
        $this->doRequest('post', $job, $groupingKey, $collectorRegistry);
    }

    /**
     * Deletes metrics from the Pushgateway.
     * Uses HTTP POST.
     * @param string $job
     * @param array|null $groupingKey
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete($job, $groupingKey = null)
    {
        $this->doRequest('delete', $job, $groupingKey);
    }

    /**
     * @param string $method
     * @param string $job
     * @param array|null $groupingKey
     * @param CollectorRegistry|null $collectorRegistry
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function doRequest($method, $job, $groupingKey, CollectorRegistry $collectorRegistry = null)
    {
        $url = $job;
        if (!empty($groupingKey)) {
            foreach ($groupingKey as $label => $value) {
                $url .= '/' . $label . '/' . $value;
            }
        }
        $requestOptions = array(
            'headers' => array(
                'Content-Type' => RenderTextFormat::MIME_TYPE
            ),
            'connect_timeout' => 10,
            'timeout' => 20,
        );
        if ($method !== 'delete') {
            if ($collectorRegistry === null) {
                throw new \RuntimeException('CollectorRegistry not set');
            }
            $renderer = new RenderTextFormat();
            $requestOptions['body'] = $renderer->render($collectorRegistry->getMetricFamilySamples());
        }
        $response = $this->client->request($method, $url, $requestOptions);
        $statusCode = $response->getStatusCode();
        if ($statusCode !== 202) {
            $msg = "Unexpected status code {$statusCode} received from pushgateway body: {$response->getBody()}";
            throw new \RuntimeException($msg);
        }
    }

}
