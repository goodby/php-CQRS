<?php

namespace Goodby\CQRS\EventSourcing\Exception;

use Exception;
use Goodby\CQRS\EventSourcing\EventStreamId;

class EventStoreException extends EventSourcingException
{
    /**
     * @param EventStreamId $identity
     * @param Exception     $because
     * @return EventStoreException
     */
    public static function cannotQueryEventStream(EventStreamId $identity, Exception $because)
    {
        return new self(
            sprintf(
                'Cannot query event stream for: %s since version: %s because: %s',
                $identity->streamName(),
                $identity->streamVersion(),
                $because->getMessage()
            ),
            null,
            $because
        );
    }
}
