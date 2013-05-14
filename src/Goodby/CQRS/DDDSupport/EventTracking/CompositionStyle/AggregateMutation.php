<?php

namespace Goodby\CQRS\DDDSupport\EventTracking\CompositionStyle;

use ReflectionClass;
use Goodby\CQRS\Assertion\Assert;
use Goodby\CQRS\DDDSupport\DomainEvent;
use Goodby\CQRS\DDDSupport\EventTracking\AggregateMutator;

class AggregateMutation
{
    /**
     * @var object
     */
    private $aggregate;

    /**
     * @var DomainEvent[]
     */
    private $mutatingEvents = [];

    /**
     * @var int
     */
    private $unmutatedVersion = 0;

    /**
     * @param string        $aggregateClassName
     * @param DomainEvent[] $domainEvents
     * @param int           $eventStreamVersion
     * @return AggregateMutation
     */
    public static function constructWithEventStream($aggregateClassName, array $domainEvents, $eventStreamVersion)
    {
        Assert::argumentIsClass($aggregateClassName, 'Aggregate class name must be existing class name');
        Assert::argumentAtLeastArray($domainEvents, 1, 'Domain events must contain at least one event');

        $aggregate = (new ReflectionClass($aggregateClassName))->newInstanceWithoutConstructor();

        $aggregateMutation = new self($aggregate);

        foreach ($domainEvents as $domainEvent) {
            $aggregateMutation->mutateWhen($domainEvent);
        }

        $aggregateMutation->unmutatedVersion = $eventStreamVersion;

        return $aggregateMutation;
    }

    /**
     * @param $aggregate
     */
    public function __construct($aggregate)
    {
        Assert::argumentIsObject($aggregate, 'Aggregate must be an object');

        $this->aggregate = $aggregate;
    }

    /**
     * @return object
     */
    public function aggregate()
    {
        return $this->aggregate;
    }

    /**
     * @return int
     */
    public function mutatedVersion()
    {
        return $this->unmutatedVersion + 1;
    }

    /**
     * @param object $aggregate
     * @return bool
     */
    public function aggregateEquals($aggregate)
    {
        return ($this->aggregate === $aggregate);
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
     * @param DomainEvent $event
     */
    public function apply(DomainEvent $event)
    {
        $this->mutatingEvents[] = $event;
        $this->mutateWhen($event);
    }

    /**
     * @param DomainEvent $domainEvent
     */
    private function mutateWhen(DomainEvent $domainEvent)
    {
        AggregateMutator::mutateWhen($this->aggregate, $domainEvent);
    }
}

