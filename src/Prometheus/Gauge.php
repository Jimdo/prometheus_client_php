<?php


namespace Prometheus;

use Prometheus\Storage\Adapter;

class Gauge extends Collector
{
    const TYPE = 'gauge';

    /**
     * @param double $value e.g. 123
     * @param array $labels e.g. ['status', 'opcode']
     */
    public function set($value, $labels = [])
    {
        $this->assertLabelsAreDefinedCorrectly($labels);

        $this->storageAdapter->updateGauge(
            [
                'name' => $this->getName(),
                'help' => $this->getHelp(),
                'type' => $this->getType(),
                'labelNames' => $this->getLabelNames(),
                'labelValues' => $labels,
                'value' => $value,
                'command' => Adapter::COMMAND_SET
            ]
        );
    }

    /**
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }

    public function inc($labels = [])
    {
        $this->incBy(1, $labels);
    }

    public function incBy($value, $labels = [])
    {
        $this->assertLabelsAreDefinedCorrectly($labels);

        $this->storageAdapter->updateGauge(
            [
                'name' => $this->getName(),
                'help' => $this->getHelp(),
                'type' => $this->getType(),
                'labelNames' => $this->getLabelNames(),
                'labelValues' => $labels,
                'value' => $value,
                'command' => Adapter::COMMAND_INCREMENT_FLOAT
            ]
        );
    }

    public function dec($labels = [])
    {
        $this->decBy(1, $labels);
    }

    public function decBy($value, $labels = [])
    {
        $this->incBy(-$value, $labels);
    }
}
