<?php

namespace Goodby\CQRS\DDDSupport\EventTracking\CompositionStyle;

use Goodby\CQRS\Assertion\Assert;
use Goodby\CQRS\DDDSupport\DomainEvent;

class AggregateMutationTracker
{
    /**
     * @var AggregateMutation[]
     */
    private $aggregateMutations = [];

    /**
     * @param string $aggregateClassName
     * @param array  $domainEvents
     * @param int    $eventStreamVersion
     * @return object
     */
    public function constructWithEventStream($aggregateClassName, array $domainEvents, $eventStreamVersion)
    {
        $aggregateMutation = AggregateMutation::constructWithEventStream($aggregateClassName, $domainEvents, $eventStreamVersion);
        $this->setAggregateMutation($aggregateMutation);

        return $aggregateMutation->aggregate();
    }

    /**
     * @param object      $aggregate
     * @param DomainEvent $domainEvent
     */
    public function apply($aggregate, DomainEvent $domainEvent)
    {
        Assert::argumentIsObject($aggregate, 'Aggregate must be an object');

        if ($this->hasAggregateMutationOf($aggregate) === false) {
            $this->setAggregateMutation(new AggregateMutation($aggregate));
        }

        $this->aggregateMutationOf($aggregate)->apply($domainEvent);
    }

    /**
     * @param object $aggregate
     * @return int
     */
    public function mutatedVersionOf($aggregate)
    {
        Assert::argumentIsObject($aggregate, 'Aggregate must be an object');

        return $this->aggregateMutationOf($aggregate)->mutatedVersion();
    }

    /**
     * @param object $aggregate
     * @return DomainEvent
     */
    public function mutatingEventsOf($aggregate)
    {
        Assert::argumentIsObject($aggregate, 'Aggregate must be an object');

        return $this->aggregateMutationOf($aggregate)->mutatingEvents();
    }

    /**
     * @param object $aggregate
     * @return int
     */
    public function unmutatedVersionOf($aggregate)
    {
        Assert::argumentIsObject($aggregate, 'Aggregate must be an object');

        return $this->aggregateMutationOf($aggregate)->unmutatedVersion();
    }

    /**
     * @param object $aggregate
     * @return AggregateMutation
     */
    private function aggregateMutationOf($aggregate)
    {
        $hash = spl_object_hash($aggregate);

        return $this->aggregateMutations[$hash];
    }

    /**
     * @param AggregateMutation $aggregateMutation
     */
    private function setAggregateMutation(AggregateMutation $aggregateMutation)
    {
        $hash = spl_object_hash($aggregateMutation->aggregate());

        $this->aggregateMutations[$hash] = $aggregateMutation;
    }

    /**
     * @param object $aggregate
     * @return bool
     */
    private function hasAggregateMutationOf($aggregate)
    {
        $hash = spl_object_hash($aggregate);

        if (array_key_exists($hash, $this->aggregateMutations)) {
            if ($this->aggregateMutations[$hash]->aggregateEquals($aggregate)) {
                return true;
            }
        }

        return false;
    }
}
