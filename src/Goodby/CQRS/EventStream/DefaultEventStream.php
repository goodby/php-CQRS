<?php

namespace Goodby\CQRS\EventStream;

use Goodby\CQRS\Assertion\Assert;
use Goodby\CQRS\DDDSupport\DomainEvent;
use Goodby\CQRS\EventSourcing\EventStream;

final class DefaultEventStream implements EventStream
{
    /**
     * @var string
     */
    private $streamVersion;

    /**
     * @var DomainEvent[]
     */
    private $events = [];

    /**
     * @param int $streamVersion
     * @param DomainEvent[] $events
     */
    public function __construct($streamVersion, array $events)
    {
        Assert::argumentAtLeast($streamVersion, 1, 'Stream version must be 1 at least');
        Assert::argumentAtLeastArray($events, 1, 'Events list must NOT be empty');

        $this->streamVersion = $streamVersion;

        foreach ($events as $event) {
            $this->addEvent($event);
        }
    }

    /**
     * @return int
     */
    public function streamVersion()
    {
        return $this->streamVersion;
    }

    /**
     * @return DomainEvent[]
     */
    public function events()
    {
        return $this->events;
    }

    /**
     * @param DomainEvent $event
     */
    private function addEvent(DomainEvent $event)
    {
        $this->events[] = $event;
    }
}
