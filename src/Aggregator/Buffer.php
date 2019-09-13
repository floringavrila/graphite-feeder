<?php

namespace Gavrila\GraphiteFeeder\Aggregator;

use Gavrila\GraphiteFeeder\Entity;

class Buffer
{
    /** @var string */
    public $metricPath;

    /** @var Retention */
    public $retention;

    /** @var \Gavrila\GraphiteFeeder\Entity\DataInterface[] */
    private $values = [];

    /** @var \Gavrila\GraphiteFeeder\Entity\DataInterface[] */
    private $readyToFlush = [];

    /** @var int */
    private $startTimestamp;

    /** @var int */
    private $endTimestamp;

    public function __construct(Retention $retention, string $metricPath)
    {
        $this->metricPath = $metricPath;
        $this->retention = $retention;
    }

    public function add(Entity\DataInterface $metric)
    {
        if (empty($this->values)) {
            $this->setCurrentBoundaries($metric);
        }

        if ($metric->getTimestamp() >= $this->endTimestamp) {
            $this->refreshBuffer();
            $this->setCurrentBoundaries($metric);
        }

        $this->values[] = $metric;

        return $this;
    }

    public function getReadyToFlushData()
    {
        $ready = $this->readyToFlush;
        $this->readyToFlush = [];

        return $ready;
    }

    public function getIncompleteData()
    {
        if (!count($this->values)) {

            return [];
        }

        $this->refreshBuffer();

        return $this->getReadyToFlushData();
    }

    private function refreshBuffer()
    {
        $this->readyToFlush[] = $this->aggregateCurrentBuffer();
        $this->values = [];
    }

    private function aggregateCurrentBuffer()
    {
        $total = 0;
        foreach ($this->values as $value) {
            $total += $value->getValue();
        }

        $value = round($total / count($this->values), 2);

        $data = new Entity\Data(
            $this->metricPath,
            $value,
            $this->startTimestamp
        );

        return $data;
    }

    private function setCurrentBoundaries(Entity\DataInterface $metric)
    {
        $retention = $this->retention->frequency;

        $percent = pow(10, strlen((string)$retention));
        $baseNumber = floor($metric->getTimestamp() / $percent) * $percent;

        $difference = $metric->getTimestamp() - ($baseNumber);

        $this->startTimestamp = $baseNumber + (floor($difference / $retention) * $retention);
        $this->endTimestamp = $this->startTimestamp + $retention;
    }
}
