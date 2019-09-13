<?php

namespace Gavrila\GraphiteFeeder\Entity;

interface DataInterface
{
    function getMetricPath();

    function getValue();

    function getTimestamp();

    function __toString();
}