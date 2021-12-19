<?php

namespace RabbiteventsMod\Events\Tests\Queue\Message;

use Interop\Amqp\AmqpMessage;
use RabbiteventsMod\Events\Queue\Message\Factory;
use RabbiteventsMod\Events\Tests\TestCase;

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
