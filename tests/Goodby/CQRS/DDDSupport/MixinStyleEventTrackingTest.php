<?php

namespace Goodby\CQRS\DDDSupport;

use Expose\ReflectionClass;
use Goodby\CQRS\DDDSupport\EventTracking\MixinStyle\EventSourcedRootEntity;
use Goodby\CQRS\FictionalDomain\Domain\Model\User;
use Goodby\CQRS\FictionalDomain\Domain\Model\UserId;
use Goodby\CQRS\FictionalDomain\Domain\Model\UserNameChanged;
use Goodby\CQRS\FictionalDomain\Domain\Model\UserRegistered;

class MixinStyleEventTrackingTest extends \PHPUnit_Framework_TestCase
{
    public function testTrackAnEvent()
    {
        $user = new User(new UserId('12345'), 'alice', 'p@ssW0rd');

        $this->assertSame('12345', $user->id()->id());
        $this->assertSame('alice', $user->name());
        $this->assertSame('p@ssW0rd', $user->password());

        $this->assertSame(0, $user->unmutatedVersion());
        $this->assertSame(1, $user->mutatedVersion());
        $this->expectedEvents(1, $user);
        $this->expectedEvent('UserRegistered', $user);
    }

    public function testTrackTwoEvents()
    {
        $user = new User(new UserId('12345'), 'alice', 'p@ssW0rd');
        $user->changeName('ALICE');

        $this->assertSame('ALICE', $user->name());
        $this->assertSame(0, $user->unmutatedVersion());
        $this->assertSame(1, $user->mutatedVersion());
        $this->expectedEvents(2, $user);
        $this->expectedEvent('UserNameChanged', $user);
    }

    public function testConstructWithEventStream()
    {
        $events = [
            new UserRegistered(new UserId('12345'), 'alice', 'p@ssW0rd'),
            new UserNameChanged(new UserId('12345'), 'ALICE'),
        ];

        $user = User::constructWithEventStream($events, 1);

        $this->assertSame('12345', $user->id()->id());
        $this->assertSame('ALICE', $user->name());
        $this->assertSame('p@ssW0rd', $user->password());
        $this->assertSame(1, $user->unmutatedVersion());
        $this->assertSame(2, $user->mutatedVersion());
        $this->expectedEvents(0, $user);
    }

    private function expectedEvents($expectedEventTotal, $aggregate)
    {
        /** @var EventSourcedRootEntity $aggregate */
        $this->assertSame($expectedEventTotal, count($aggregate->mutatingEvents()));
    }

    private function expectedEvent($expectedEventName, $aggregate)
    {
        /** @var EventSourcedRootEntity $aggregate */
        $events = $aggregate->mutatingEvents();

        $bool = false;

        foreach ($events as $event) {
            if ((new ReflectionClass($event))->getShortName() === $expectedEventName) {
                $bool = true;
            }
        }

        $this->assertTrue($bool, sprintf("Expected domain event %s", $expectedEventName));
    }
}
