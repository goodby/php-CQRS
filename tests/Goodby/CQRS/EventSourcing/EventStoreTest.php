<?php

namespace Goodby\CQRS\EventSourcing;

use Expose\ReflectionClass;
use Goodby\CQRS\EventSerialization\JSON\JSONSerializer;
use Goodby\CQRS\EventSourcing\Exception\EventStoreAppendException;
use Goodby\CQRS\EventSourcing\Exception\EventStreamNotExistException;
use Goodby\CQRS\EventStore\InMemory\InMemoryEventStore;
use Goodby\CQRS\FictionalDomain\Domain\Model\UserId;
use Goodby\CQRS\FictionalDomain\Domain\Model\UserNameChanged;
use Goodby\CQRS\FictionalDomain\Domain\Model\UserRegistered;

class EventStoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return EventStore
     */
    private function eventStore()
    {
        $serializer = new JSONSerializer();
        $eventStore = new InMemoryEventStore($serializer);

        return $eventStore;
    }

    public function testAppendEventAndThenRetrieveEventStream()
    {
        $eventStore = $this->eventStore();

        $eventStore->append(new EventStreamId('user.123'), [
            new UserRegistered(new UserId('123'), 'alice', 'p@ssWord'),
            new UserNameChanged(new UserId('123'), 'ALICE'),
        ]);

        $eventStream = $eventStore->eventStreamSince(new EventStreamId('user.123'));

        $this->assertSame(2, $eventStream->streamVersion());
        $this->assertCount(2, $eventStream->events());
        $this->expectEvent('UserRegistered', $eventStream);
        $this->expectEvent('UserNameChanged', $eventStream);
    }

    public function testRetrieveEventSinceVersion()
    {
        $eventStore = $this->eventStore();

        $eventStore->append(new EventStreamId('user.123'), [
            new UserRegistered(new UserId('123'), 'alice', 'p@ssWord'),
            new UserNameChanged(new UserId('123'), 'ALICE'),
        ]);

        $sinceVersion = 2;
        $eventStream = $eventStore->eventStreamSince(new EventStreamId('user.123', $sinceVersion));

        $this->assertSame(2, $eventStream->streamVersion());
        $this->assertCount(1, $eventStream->events());
        $this->expectEvent('UserNameChanged', $eventStream);
    }

    public function testRetrievingNotExistingEventStream()
    {
        try {
            $eventStore = $this->eventStore();
            $eventStore->eventStreamSince(new EventStreamId('non-existing-event-stream'));
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof EventStreamNotExistException);

            return;
        }

        $this->fail('Expects exception.');
    }

    public function testRetrievingNotExistingStreamVersion()
    {
        try {
            $eventStore = $this->eventStore();
            $eventStore->append(new EventStreamId('user.123'), [
                new UserRegistered(new UserId('123'), 'alice', 'p@ssWord'),
                new UserNameChanged(new UserId('123'), 'ALICE'),
            ]);
            $nonExistingStreamVersion = 3;
            $eventStore->eventStreamSince(new EventStreamId('user.123', $nonExistingStreamVersion));
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof EventStreamNotExistException);

            return;
        }

        $this->fail('Expects exception.');
    }

    public function testAppendEventButConflict()
    {
        try {
            $eventStore = $this->eventStore();
            $eventStore->append(new EventStreamId('user.123'), [
                new UserRegistered(new UserId('123'), 'alice', 'p@ssWord'),
            ]);
            $eventStore->append(new EventStreamId('user.123'), [
                new UserRegistered(new UserId('123'), 'alice', 'p@ssWord'),
            ]);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof EventStoreAppendException);

            return;
        }

        $this->fail('Expects exception.');
    }

    /**
     * @param string      $eventName
     * @param EventStream $eventStream
     */
    private function expectEvent($eventName, EventStream $eventStream)
    {
        $found = false;

        foreach ($eventStream->events() as $event) {
            $className = (new ReflectionClass($event))->getShortName();

            if ($className === $eventName) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, "Expects a domain event: ".$eventName);
    }
}
