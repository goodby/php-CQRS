<?php

namespace Goodby\CQRS\DDDSupport;

use DateTime;

interface DomainEvent
{
    /**
     * @return int
     */
    public function eventVersion();

    /**
     * @return DateTime
     */
    public function occurredOn();

    /**
     * @return array
     */
    public function toContractualData();

    /**
     * @param array $data
     * @return DomainEvent
     */
    public static function fromContractualData(array $data);
}
