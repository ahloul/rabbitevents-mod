<?php

namespace Ecavalier\Events\Tests\Queue\Message;

use Interop\Amqp\AmqpMessage;
use Ecavalier\Events\Queue\Message\Factory;
use Ecavalier\Events\Tests\TestCase;

class FactoryTest extends TestCase
{
    public function testMake()
    {
        $payload = ['some' => 'payload'];
        $factory = new Factory();

        $result = $factory->make('event', $payload);

        self::assertInstanceOf(AmqpMessage::class, $result);
        self::assertEquals('event', $result->getRoutingKey());
        self::assertEquals(json_encode($payload), $result->getBody());
    }
}
