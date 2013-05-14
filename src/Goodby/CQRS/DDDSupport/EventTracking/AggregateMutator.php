<?php

namespace Goodby\CQRS\DDDSupport\EventTracking;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Goodby\CQRS\Assertion\Assert;
use Goodby\CQRS\DDDSupport\DomainEvent;

class AggregateMutator
{
    /**
     * @param object      $aggregate
     * @param DomainEvent $domainEvent
     * @param string      $mutatorMethodFormat
     * @throws AggregateMutateException
     * @throws AggregateMutateException
     */
    public static function mutateWhen($aggregate, DomainEvent $domainEvent, $mutatorMethodFormat = 'when{EventClassName}')
    {
        Assert::argumentIsObject($aggregate, 'Aggregate must be an object');

        $eventClassName = (new ReflectionClass($domainEvent))->getShortName();
        $mutatorMethodName = str_replace('{EventClassName}', $eventClassName, $mutatorMethodFormat);

        if (method_exists($aggregate, $mutatorMethodName) === false) {
            throw AggregateMutateException::mutatorMethodNotExist(get_class($aggregate), $mutatorMethodName, $eventClassName);
        }

        try {
            $mutatorMethod = new ReflectionMethod($aggregate, $mutatorMethodName);
            $mutatorMethod->setAccessible(true);
            $mutatorMethod->invoke($aggregate, $domainEvent);
        } catch (ReflectionException $because) {
            throw AggregateMutateException::because($because);
        }
    }
}
