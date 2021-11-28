<?php

namespace Ecavalier\Events\Tests\Amqp;

use Interop\Amqp\AmqpBind;
use Interop\Amqp\AmqpQueue;
use Interop\Amqp\AmqpTopic;
use Ecavalier\Events\Amqp\BindFactory;
use Ecavalier\Events\Queue\Context;
use Ecavalier\Events\Tests\TestCase;

class BindFactoryTest extends TestCase
{
    public function testMake()
    {
        $topic  = \Mockery::mock(AmqpTopic::class);

        $context = \Mockery::mock(Context::class);
        $context->shouldReceive('topic')
            ->andReturn($topic);
        $queue = \Mockery::mock(AmqpQueue::class);

        $factory = new BindFactory($context);
        $bind = $factory->make($queue, 'item.created');

        self::assertInstanceOf(AmqpBind::class, $bind);

        self::assertEquals($topic, $bind->getTarget());
        self::assertEquals($queue, $bind->getSource());
        self::assertEquals('item.created', $bind->getRoutingKey());
    }
}
