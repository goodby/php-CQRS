<?php

namespace Goodby\CQRS\EventStore\InMemory;

use Goodby\CQRS\DDDSupport\DomainEvent;
use Goodby\CQRS\EventSourcing\EventStore;
use Goodby\CQRS\EventSourcing\EventStream;
use Goodby\CQRS\EventSourcing\EventStreamId;
use Goodby\CQRS\EventSourcing\Exception\EventStoreAppendException;
use Goodby\CQRS\EventSourcing\Exception\EventStoreException;
use Goodby\CQRS\EventSourcing\Exception\EventStreamNotExistException;
use Goodby\CQRS\EventStream\DefaultEventStream;
use Goodby\CQRS\EventSerialization\Serializer;

class InMemoryEventStore implements EventStore
{
    private $eventStreams = [];

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param EventStreamId $startingIdentity
     * @param DomainEvent[]       $events
     * @return void
     * @throws EventStoreAppendException
     */
    public function append(EventStreamId $startingIdentity, array $events)
    {
        $index = 0;

        foreach ($events as $event) {
            $this->appendEvent(
                $startingIdentity->streamName(),
                $startingIdentity->streamVersion() + $index,
                $event
            );
            $index += 1;
        }
    }

    /**
     * @param EventStreamId $since
     * @return EventStream
     * @throws EventStreamNotExistException
     * @throws EventStoreException
     */
    public function eventStreamSince(EventStreamId $since)
    {
        if (isset($this->eventStreams[$since->streamName()][$since->streamVersion()]) === false) {
            throw EventStreamNotExistException::noStream($since);
        }

        $version = 0;
        $events = [];

        foreach ($this->eventStreams[$since->streamName()] as $version => $serializedEvent) {
            if ($version < $since->streamVersion()) {
                continue;
            }

            $events[] = $this->serializer->deserialize(
                $serializedEvent['type'],
                $serializedEvent['body']
            );
        }

        return new DefaultEventStream($version, $events);
    }

    /**
     * @param string $streamName
     * @param int $streamVersion
     * @param DomainEvent $event
     * @throws EventStoreAppendException
     */
    private function appendEvent($streamName, $streamVersion, DomainEvent $event)
    {
        if (isset($this->eventStreams[$streamName][$streamVersion])) {
            throw EventStoreAppendException::conflictedAppending($streamName, $streamVersion);
        }

        if (array_key_exists($streamName, $this->eventStreams) === false) {
            $this->eventStreams[$streamName] = [];
        }

        $this->eventStreams[$streamName][$streamVersion] = [
            'type' => get_class($event),
            'body' => $this->serializer->serialize($event),
        ];
    }
}
