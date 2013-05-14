<?php

namespace Goodby\CQRS\FictionalDomain\Port\Adapter\Persistence\Repository;

use Goodby\CQRS\EventSourcing\EventStore;
use Goodby\CQRS\EventSourcing\EventStreamId;
use Goodby\CQRS\FictionalDomain\Domain\Model\User;
use Goodby\CQRS\FictionalDomain\Domain\Model\UserId;
use Goodby\CQRS\FictionalDomain\Domain\Model\UserRepository;

class EventStoreUserRepository implements UserRepository
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @param EventStore $eventStore
     */
    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * @return UserId
     */
    public function nextIdentity()
    {
        return new UserId(md5(uniqid()));
    }

    /**
     * @param UserId $userId
     * @return User
     */
    public function userOfId(UserId $userId)
    {
        // snapshots not currently supported; always use version 1

        $eventStream = $this->eventStore->eventStreamSince(
            new EventStreamId($userId->id())
        );
        return User::constructWithEventStream(
            $eventStream->events(),
            $eventStream->streamVersion()
        );
    }

    /**
     * @param User $user
     * @return void
     */
    public function save(User $user)
    {
        $this->eventStore->append(
            new EventStreamId(
                $user->id()->id(),
                $user->mutatedVersion()
            ),
            $user->mutatingEvents()
        );
    }
}
