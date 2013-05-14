<?php

namespace Goodby\CQRS\EventSourcing;

use Goodby\CQRS\DDDSupport\DomainEvent;
use Goodby\CQRS\EventSourcing\Exception\EventStoreAppendException;
use Goodby\CQRS\EventSourcing\Exception\EventStoreException;
use Goodby\CQRS\EventSourcing\Exception\EventStreamNotExistException;

interface EventStore
{
    /**
     * @param EventStreamId $startingIdentity
     * @param DomainEvent[] $events
     * @return void
     * @throws EventStoreAppendException
     */
    public function append(EventStreamId $startingIdentity, array $events);

    /**
     * @param EventStreamId $eventStreamId
     * @return EventStream
     * @throws EventStreamNotExistException
     * @throws EventStoreException
     */
    public function eventStreamSince(EventStreamId$eventStreamId);
}
