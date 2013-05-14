<?php

namespace Goodby\CQRS\EventStore\MySQLPDO;

use Exception;
use PDO;
use PDOStatement;
use Goodby\CQRS\Assertion\Assert;
use Goodby\CQRS\DDDSupport\DomainEvent;
use Goodby\CQRS\EventSourcing\EventStore;
use Goodby\CQRS\EventSourcing\EventStream;
use Goodby\CQRS\EventSourcing\EventStreamId;
use Goodby\CQRS\EventSourcing\Exception\EventStoreAppendException;
use Goodby\CQRS\EventSourcing\Exception\EventStoreException;
use Goodby\CQRS\EventSourcing\Exception\EventStreamNotExistException;
use Goodby\CQRS\EventStream\DefaultEventStream;
use Goodby\CQRS\EventSerialization\Serializer;

class MySQLPDOEventStore implements EventStore
{
    /**
     * @var PDO
     */
    private $connection;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @param PDO        $connection
     * @param Serializer $serializer
     * @param string     $tableName
     */
    public function __construct(PDO $connection, Serializer $serializer, $tableName)
    {
        Assert::argumentNotEmpty($tableName, 'Table name is required.');

        $this->connection = $connection;
        $this->serializer = $serializer;
        $this->tableName = $tableName;
    }

    /**
     * @param EventStreamId $startingIdentity
     * @param DomainEvent[]       $events
     * @return void
     * @throws EventStoreAppendException
     */
    public function append(EventStreamId $startingIdentity, array $events)
    {
        try {
            $this->connection->beginTransaction();

            $statement = $this->connection->prepare(
                sprintf(
                    'INSERT INTO `%s` (stream_name, stream_version, event_type, event_body) '
                        .'VALUES (:stream_name, :stream_version, :event_type, :event_body)',
                    $this->tableName
                )
            );

            $streamIndex = 0;

            foreach ($events as $event) {
                $this->appendEvent(
                    $statement,
                    $startingIdentity->streamName(),
                    $startingIdentity->streamVersion() + $streamIndex,
                    $event
                );
                $streamIndex += 1;
            }

            $this->connection->commit();
        } catch (Exception $because) {
            $this->connection->rollBack();
            throw EventStoreAppendException::because($because);
        }
    }

    /**
     * @param EventStreamId $eventStreamId
     * @throws EventStoreException
     * @throws EventStreamNotExistException
     * @return EventStream
     */
    public function eventStreamSince(EventStreamId $eventStreamId)
    {
        try {
            $statement = $this->connection->prepare(
                sprintf(
                    'SELECT stream_version, event_type, event_body FROM %s '
                        .'WHERE stream_name = :stream_name AND stream_version >= :stream_version '
                        .'ORDER BY stream_version',
                    $this->tableName
                )
            );

            $statement->bindValue(':stream_name', $eventStreamId->streamName(), PDO::PARAM_STR);
            $statement->bindValue(':stream_version', $eventStreamId->streamVersion(), PDO::PARAM_INT);
            $statement->execute();

            if ($statement->rowCount() === 0) {
                throw EventStreamNotExistException::noStream($eventStreamId);
            }

            $eventStream = $this->buildEventStream($statement);

            return $eventStream;
        } catch (EventStreamNotExistException $e) {
            throw $e; // escalation
        } catch (Exception $because) {
            throw EventStoreException::cannotQueryEventStream($eventStreamId, $because);
        }
    }

    /**
     * @param PDOStatement $statement
     * @param string       $streamName
     * @param int          $streamVersion
     * @param DomainEvent        $event
     */
    private function appendEvent(PDOStatement $statement, $streamName, $streamVersion, DomainEvent $event)
    {
        $statement->bindValue(':stream_name', $streamName, PDO::PARAM_STR);
        $statement->bindValue(':stream_version', $streamVersion, PDO::PARAM_INT);
        $statement->bindValue(':event_type', get_class($event), PDO::PARAM_STR);
        $statement->bindValue(':event_body', $this->serializer->serialize($event), PDO::PARAM_STR);
        $statement->execute();
    }

    /**
     * @param PDOStatement $resultSet
     * @return DefaultEventStream
     */
    private function buildEventStream(PDOStatement $resultSet)
    {
        $version = 0;
        $events = [];

        foreach ($resultSet as $result) {
            $version = $result['stream_version'];
            $events[] = $this->serializer->deserialize(
                $result['event_type'],
                $result['event_body']
            );;
        }

        return new DefaultEventStream($version, $events);
    }
}
