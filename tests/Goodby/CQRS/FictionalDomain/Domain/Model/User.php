<?php

namespace Goodby\CQRS\FictionalDomain\Domain\Model;

use Goodby\CQRS\DDDSupport\EventTracking\MixinStyle\EventSourcedRootEntity;

/**
 * <<aggregate root>>
 */
class User
{
    use EventSourcedRootEntity;

    /**
     * @var UserId
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $password;

    /**
     * @param UserId $id
     * @param string $name
     * @param string $password
     */
    public function __construct(UserId $id, $name, $password)
    {
        $this->apply(new UserRegistered($id, $name, $password));
    }

    public function changeName($newName)
    {
        $this->apply(new UserNameChanged($this->id, $newName));
    }

    /**
     * @return UserId
     */
    public function id()
    {
        return $this->id;
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
     * @param UserRegistered $event
     */
    private function whenUserRegistered(UserRegistered $event)
    {
        $this->id = $event->userId();
        $this->name = $event->name();
        $this->password = $event->password();
    }

    /**
     * @param UserNameChanged $event
     */
    private function whenUserNameChanged(UserNameChanged $event)
    {
        $this->name = $event->name();
    }
}
