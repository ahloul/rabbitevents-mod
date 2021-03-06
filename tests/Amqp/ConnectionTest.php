<?php

namespace RabbiteventsMod\Events\Tests\Amqp;

use Enqueue\AmqpTools\DelayStrategy;
use Interop\Amqp\AmqpConnectionFactory;
use RabbiteventsMod\Events\Amqp\Connection;
use RabbiteventsMod\Events\Queue\Context;
use RabbiteventsMod\Events\Tests\TestCase;

class ConnectionTest extends TestCase
{
    /**
     * @var Connection
     */
    private $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = new Connection(['exchange' => 'events']);
    }

    public function testConnect()
    {
        self::assertInstanceOf(AmqpConnectionFactory::class, $this->connection->connect());
    }

    public function testDelayStrategySetter()
    {
        $strategy = \Mockery::mock(DelayStrategy::class);
        $this->connection->setDelayStrategy($strategy);

        self::assertSame($strategy, $this->connection->getDelayStrategy());
    }

    public function testGetDelayStrategy()
    {
        self::assertInstanceOf(DelayStrategy::class, $this->connection->getDelayStrategy());
    }
}
