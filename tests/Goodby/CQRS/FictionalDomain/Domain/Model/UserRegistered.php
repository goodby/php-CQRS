<?php

namespace Goodby\CQRS\FictionalDomain\Domain\Model;

use DateTime;
use Goodby\CQRS\DDDSupport\DomainEvent;

class UserRegistered implements DomainEvent
{
    /**
     * @var UserId
     */
    private $userId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $password;

    /**
     * @var int
     */
    private $eventVersion;

    /**
     * @var DateTime
     */
    private $occurredOn;

    /**
     * @param UserId $userId
     * @param string $name
     * @param string $password
     */
    public function __construct(UserId $userId, $name, $password)
    {
        $this->userId = $userId;
        $this->name = $name;
        $this->password = $password;
        $this->eventVersion = 1;
        $this->occurredOn = new DateTime();
    }

    /**
     * @return UserId
     */
    public function userId()
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function password()
    {
        return $this->password;
    }

    /**
     * @return int
     */
    public function eventVersion()
    {
        return $this->eventVersion;
    }

    /**
     * @return DateTime
     */
    public function occurredOn()
    {
        return $this->occurredOn;
    }

    /**
     * @return array
     */
    public function toContractualData()
    {
        return [
            'userId'       => $this->userId()->id(),
            'name'         => $this->name(),
            'password'     => $this->password(),
            'eventVersion' => $this->eventVersion(),
            'occurredOn'   => $this->occurredOn()->getTimestamp(),
        ];
    }

    /**
     * @param array $data
     * @return DomainEvent
     */
    public static function fromContractualData(array $data)
    {
        $event = new self(new UserId($data['userId']), $data['name'], $data['password']);
        $event->eventVersion = $data['eventVersion'];
        $event->occurredOn = (new DateTime)->setTimestamp($data['occurredOn']);

        return $event;
    }
}
