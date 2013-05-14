<?php

namespace Goodby\CQRS\EventSourcing\Exception;

use Exception;

class EventStoreAppendException extends EventSourcingException
{
    /**
     * @param string $eventStreamName
     * @param int $eventStreamVersion
     * @return EventStoreAppendException
     */
    public static function conflictedAppending($eventStreamName, $eventStreamVersion)
    {
        return new self(
            sprintf(
                'Can not append to event stream: %s : %s because: the event conflicts another event',
                $eventStreamName,
                $eventStreamVersion
            )
        );
    }

    /**
     * @param Exception $because
     * @return EventStoreAppendException
     */
    public static function because(Exception $because)
    {
        return new self(
            sprintf('Could not append to event store, because: %s', $because->getMessage()),
            null,
            $because
        );
    }
}
