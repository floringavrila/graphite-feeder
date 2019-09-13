<?php

namespace Gavrila\GraphiteFeeder\Connector;

use Gavrila\GraphiteFeeder\Exception;

Class Fsock implements ConnectorInterface
{
    /** @var int */
    private $port;

    /** @var string */
    private $host;

    /** @var string */
    private $protocol;

    /** @var int */
    private $timeout;

    public function __construct($hostname = 'localhost', $port = 2003, $protocol = 'udp', $timeout = null)
    {
        if (!in_array($protocol, ['udp', 'tcp', 'ssl', 'tls'])) {
            throw new Exception\InvalidArgumentException(sprintf('Invalid protocol %s', $protocol));
        }

        $this->host = $hostname;
        $this->port = $port;
        $this->protocol = $protocol;

        if (is_null($timeout)) {
            $this->timeout = ini_get('default_socket_timeout');
        }
    }

    public function open()
    {
        $handle = fsockopen(
            sprintf('%s://%s', $this->protocol, $this->getHost()),
            $this->getPort(),
            $errno,
            $errStr,
            $this->getTimeout()
        );

        if (!$handle) {
            throw new Exception\ConnectionException('(' . $errno . ') ' . $errStr);
        }

        return $handle;
    }

    public function write($handle, $message)
    {
        return @fwrite($handle, $message);
    }

    public function flush($handle)
    {
        fflush($handle);
    }

    public function close($handle)
    {
        fclose($handle);
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getProtocol()
    {
        return $this->protocol;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }
}