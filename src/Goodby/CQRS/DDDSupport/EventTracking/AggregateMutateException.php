<?php

namespace Goodby\CQRS\DDDSupport\EventTracking;

use ReflectionException;
use RuntimeException;

class AggregateMutateException extends RuntimeException
{
    /**
     * @param string  $aggregateClassName
     * @param string  $mutatorMethodName
     * @param string  $eventClassName
     * @return AggregateMutateException
     */
    public static function mutatorMethodNotExist($aggregateClassName, $mutatorMethodName, $eventClassName)
    {
        return new self(
            sprintf(
                'Mutator method not found: %s::%s(%s $event)',
                $aggregateClassName,
                $mutatorMethodName,
                $eventClassName
            )
        );
    }

    /**
     * @param ReflectionException $because
     * @return AggregateMutateException
     */
    public static function because(ReflectionException $because)
    {
        return new self(
            sprintf('Aggregate mutation failed, because: %s', $because->getMessage()),
            null,
            $because
        );
    }
}