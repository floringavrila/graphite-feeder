<?php

namespace Gavrila\GraphiteFeeder\Entity;

class Data implements DataInterface
{
    /** @var string */
    private $metricPath;
    /** @var string */
    private $value;
    /** @var string */
    private $timestamp;

    public function __construct($metric, $value, $timestamp)
    {
        $this->metricPath = $metric;
        $this->value = $value;
        $this->timestamp = $timestamp;
    }

    /**
     * @return string
     */
    public function getMetricPath()
    {
        return $this->metricPath;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return sprintf('%s %s %s', $this->getMetricPath(), $this->getValue(), $this->getTimestamp());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getMessage();
    }
}