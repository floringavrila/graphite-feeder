<?php

namespace Gavrila\GraphiteFeeder\Connector;

use Gavrila\GraphiteFeeder\Exception\InvalidArgumentException;

Class Socket implements ConnectorInterface
{
    /** @var int */
    private $port;

    /** @var string */
    private $host;

    /** @var string */
    private $protocol;

    public function __construct($hostname = 'localhost', $port = 2003, $protocol = 'udp')
    {
        $this->host = $hostname;
        $this->port = $port;

        switch ($protocol) {
            case 'udp':
                $this->protocol = SOL_UDP;
                break;
            case 'tcp':
                $this->protocol = SOL_TCP;
                break;
            default:
                throw new InvalidArgumentException(sprintf('Invalid protocol %s', $protocol));
                break;
        }
    }

    public function open()
    {
        if ($this->protocol == SOL_UDP) {
            return socket_create(AF_INET, SOCK_DGRAM, $this->getProtocol());
        } else {
            return socket_create(AF_INET, SOCK_STREAM, $this->getProtocol());
        }
    }

    public function write($handle, $message)
    {
        return socket_sendto($handle, $message, strlen($message), 0, $this->getHost(), $this->getPort());
    }

    public function flush($handle)
    {
        return;
    }

    public function close($handle)
    {
        socket_close($handle);
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
}