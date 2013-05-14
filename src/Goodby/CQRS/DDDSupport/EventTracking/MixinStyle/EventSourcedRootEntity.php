<?php

namespace Goodby\CQRS\DDDSupport\EventTracking\MixinStyle;

use ReflectionClass;
use Goodby\CQRS\DDDSupport\DomainEvent;
use Goodby\CQRS\DDDSupport\EventTracking\AggregateMutator;

trait EventSourcedRootEntity
{
    /**
     * @var DomainEvent[]
     */
    private $mutatingEvents = [];

    /**
     * @var int
     */
    private $unmutatedVersion = 0;

    /**
     * @param DomainEvent[] $domainEvents
     * @param int           $eventStreamVersion
     * @return $this
     */
    public static function constructWithEventStream(array $domainEvents, $eventStreamVersion)
    {
        $self = (new ReflectionClass(get_called_class()))->newInstanceWithoutConstructor();

        foreach ($domainEvents as $domainEvent) {
            $self->mutateWhen($domainEvent);
        }

        $self->setUnmutatedVersion($eventStreamVersion);

        return $self;
    }

    /**
     * @return int
     */
    public function mutatedVersion()
    {
        return $this->unmutatedVersion + 1;
    }

    /**
     * @return DomainEvent[]
     */
    public function mutatingEvents()
    {
        return $this->mutatingEvents;
    }

    /**
     * @return int
     */
    public function unmutatedVersion()
    {
        return $this->unmutatedVersion;
    }

    /**
     * @param DomainEvent $domainEvent
     */
    private function apply(DomainEvent $domainEvent)
    {
        $this->mutatingEvents[] = $domainEvent;
        $this->mutateWhen($domainEvent);
    }

    /**
     * @param DomainEvent $domainEvent
     */
    private function mutateWhen(DomainEvent $domainEvent)
    {
        AggregateMutator::mutateWhen($this, $domainEvent);
    }

    /**
     * @param int $streamVersion
     */
    private function setUnmutatedVersion($streamVersion)
    {
        $this->unmutatedVersion = $streamVersion;
    }
}
