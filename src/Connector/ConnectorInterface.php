<?php

namespace Gavrila\GraphiteFeeder\Connector;

Interface ConnectorInterface
{
    function open();

    function write($handle, $message);

    function flush($handle);

    function close($handle);
}