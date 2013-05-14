<?php

namespace Goodby\CQRS\EventSourcing;

use Goodby\CQRS\DDDSupport\DomainEvent;

interface EventStream
{
    /**
     * @return int
     */
    public function streamVersion();

    /**
     * @return DomainEvent[]
     */
    public function events();
}
