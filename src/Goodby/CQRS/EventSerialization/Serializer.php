<?php

namespace Goodby\CQRS\EventSerialization;

use Goodby\CQRS\DDDSupport\DomainEvent;

interface Serializer
{
    /**
     * @param DomainEvent $event
     * @return string
     */
    public function serialize(DomainEvent $event);

    /**
     * @param string $eventClass
     * @param string $serialization
     * @return DomainEvent
     */
    public function deserialize($eventClass, $serialization);
}
