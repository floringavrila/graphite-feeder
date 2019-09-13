<?php

namespace Gavrila\GraphiteFeeder\Aggregator;

use Gavrila\GraphiteFeeder\Entity\DataInterface;
use Gavrila\GraphiteFeeder\Exception;
use Carbon\Carbon;

class DataCollection
{
    /** @var \Gavrila\GraphiteFeeder\Aggregator\Retention[] */
    private $retentions = [];

    /** @var \Gavrila\GraphiteFeeder\Aggregator\Buffer[] */
    private $buffers = [];

    /** @var \Gavrila\GraphiteFeeder\Entity\DataInterface[] */
    private $data = [];

    /** @var string */
    private $dataTimeZone;

    /**
     * DataCollection constructor.
     * Specify retentions from most-precise:least-history to least-precise:most-history
     *
     * @param \Gavrila\GraphiteFeeder\Aggregator\Retention[] $retentions
     * @param string $dataTimeZone
     */
    public function __construct(array $retentions, $dataTimeZone = 'UTC')
    {
        $this->dataTimeZone = $dataTimeZone;
        $this->retentions = $retentions;
    }

    public function add(DataInterface $metric)
    {
        $buffer = $this->getBufferForMetric($metric)->add($metric);
        $this->data = array_merge($this->data, $buffer->getReadyToFlushData());

        return $this;
    }


    public function getReadyData()
    {
        $data = $this->data;
        $this->data = [];

        return $data;
    }

    public function getAllData()
    {

        $data = $this->getReadyData();

        foreach ($this->buffers as $buffer) {
            $data = array_merge($data, $buffer->getIncompleteData());
        }

        return $data;
    }

    private function getBufferForMetric(DataInterface $metric)
    {
        $retention = $this->determineRetention($metric);
        foreach ($this->buffers as $buffer) {
            if (
                strcmp($buffer->metricPath, $metric->getMetricPath()) === 0
                && $retention === $buffer->retention
            ) {
                return $buffer;
            }
        }

        return $this->createBuffer($retention, $metric);
    }

    private function determineRetention(DataInterface $metric)
    {
        $now = Carbon::now($this->dataTimeZone);

        $timeline = 0;
        foreach ($this->retentions as $retention) {
            $timeline +=  $retention->history;
            if ($metric->getTimestamp() >= $now->copy()->subSeconds($timeline)->getTimestamp()) {

                return $retention;
            }
        }

        throw new Exception\RetentionNotMatched('Did not match any retention settings');
    }

    private function createBuffer(Retention $retention, DataInterface $metric)
    {
        $buffer = new Buffer($retention, $metric->getMetricPath());
        $this->buffers[] = $buffer;

        return $buffer;
    }
}
