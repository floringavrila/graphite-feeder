<?php

namespace Gavrila\GraphiteFeeder\Aggregator;

use Gavrila\GraphiteFeeder\Exception\InvalidArgumentException;

class Retention
{
    /** @var int number of seconds */
    public $frequency;

    /** @var int number of seconds */
    public $history;

    const UNIT_MULTIPLIERS = [
        's' => 1,
        'm' => 60,
        'h' => 3600,
        'd' => 86400,
        'w' => 86400 * 7,
        'y' => 86400 * 365
    ];

    /**
     * @param string $retention
     */
    public function __construct($retention)
    {
        $this->parseRetentionString($retention);
    }

    /**
     * @param string $retention
     */
    private function parseRetentionString($retention)
    {
        $parts = explode(':', $retention);
        if (count($parts) != 2) {
            throw new InvalidArgumentException(sprintf('Invalid retention format %s', $retention));
        }

        $this->frequency = $this->getUnitSeconds($parts[0]);
        $this->history = $this->getUnitSeconds($parts[1]);
    }

    /**
     * @param string $timeLength
     *
     * @return int
     */
    private function getUnitSeconds($timeLength)
    {
        preg_match('/^(\d+)([a-z]+)$/', $timeLength, $frequencyMatches);
        if (count($frequencyMatches) != 3) {
            throw new InvalidArgumentException(
                sprintf('Invalid time length definition format (%s)', $timeLength)
            );
        }

        if (!array_key_exists($frequencyMatches[2], self::UNIT_MULTIPLIERS)) {
            throw new InvalidArgumentException(
                sprintf('Invalid time unit %s ', $frequencyMatches[2])
            );
        }

        return $frequencyMatches[1] * self::UNIT_MULTIPLIERS[$frequencyMatches[2]];
    }
}
