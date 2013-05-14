<?php

namespace Goodby\CQRS\EventSerialization\JSON;

use Goodby\CQRS\Assertion\Assert;
use Goodby\CQRS\DDDSupport\DomainEvent;
use Goodby\CQRS\EventSerialization\Serializer;

class JSONSerializer implements Serializer
{
    /**
     * @param DomainEvent $event
     * @return string
     */
    public function serialize(DomainEvent $event)
    {
        return json_encode($event->toContractualData(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param string $eventClass
     * @param string $serialization
     * @return DomainEvent
     */
    public function deserialize($eventClass, $serialization)
    {
        Assert::argumentNotEmpty($eventClass, 'Event class name is required');
        Assert::argumentSubclass($eventClass, 'Event class must be subclass of', 'Goodby\CQRS\DDDSupport\DomainEvent');
        /** @var $eventClass DomainEvent */
        return $eventClass::fromContractualData(json_decode($serialization, true));
    }
}
