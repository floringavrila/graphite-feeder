<?php

namespace Gavrila\GraphiteFeeder;

use Gavrila\GraphiteFeeder\Aggregator\DataCollection;
use Gavrila\GraphiteFeeder\Connector\ConnectorInterface;
use Gavrila\GraphiteFeeder\Aggregator\Retention;

class Client
{
    /** @var \Gavrila\GraphiteFeeder\Connector\ConnectorInterface */
    private $connector;

    /** @var \Gavrila\GraphiteFeeder\Aggregator\DataCollection */
    public $dataBuffer;

    /**
     * Client constructor.
     *
     * @param \Gavrila\GraphiteFeeder\Connector\ConnectorInterface $connector
     * @param string $retentions Specify retentions from most-precise:least-history to least-precise:most-history
     */
    public function __construct(ConnectorInterface $connector, $retentions)
    {
        $this->connector = $connector;
        $this->dataBuffer = new DataCollection($this->parseRetentionString($retentions));
    }

    public function flushCompletedBuffers()
    {
        $written = $this->send($this->dataBuffer->getReadyData());

        return $written;
    }

    public function flushAllData()
    {
        return $this->send($this->dataBuffer->getAllData());
    }

    /**
     * send array of  \Gavrila\GraphiteFeeder\Entity\DataInterface
     * or array of strings
     *
     * @param \Gavrila\GraphiteFeeder\Entity\DataInterface[] $data
     * @return int bytes written
     */
    private function send(array $data)
    {
        if (empty($data)) {
            return 0;
        }

        $handle = $this->connector->open();

        $batchMessage = implode("\n", $data) . "\n";

        $written = $this->connector->write($handle, $batchMessage);
        $this->connector->close($handle);

        return $written;
    }

    /**
     * @param string $retentions
     * @return \Gavrila\GraphiteFeeder\Aggregator\Retention[] $retentions
     */
    private function parseRetentionString($retentions)
    {
        $objects = [];
        foreach (explode(',', $retentions) as $retention) {
            $objects[] = new Retention($retention);
        }

        return $objects;
    }
}