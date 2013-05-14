<?php

namespace Goodby\CQRS\EventSourcing;

final class EventStreamId
{
    /**
     * @var string
     */
    private $streamName;

    /**
     * @var int
     */
    private $streamVersion;

    /**
     * @param string $streamName
     * @param int $streamVersion
     */
    public function __construct($streamName, $streamVersion = 1)
    {
        $this->streamName = $streamName;
        $this->streamVersion = $streamVersion;
    }
    
    /**
     * @return string
     */
    public function streamName()
    {
        return $this->streamName;
    }

    /**
     * @return int
     */
    public function streamVersion()
    {
        return $this->streamVersion;
    }
}
