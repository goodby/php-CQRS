<?php

namespace Goodby\CQRS\EventSourcing\Exception;

use Goodby\CQRS\EventSourcing\EventStreamId;

class EventStreamNotExistException extends EventSourcingException
{
    /**
     * @param EventStreamId $eventStreamId
     * @return EventStreamNotExistException
     */
    public static function noStream(EventStreamId $eventStreamId)
    {
        return new self(
            sprintf(
                'There is no such event stream: %s : %s',
                $eventStreamId->streamName(),
                $eventStreamId->streamVersion()
            )
        );
    }
}
