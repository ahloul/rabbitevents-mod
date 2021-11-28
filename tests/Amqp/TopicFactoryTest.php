<?php

namespace Ecavalier\Events\Tests\Amqp;

use Enqueue\AmqpLib\AmqpContext;
use Interop\Amqp\AmqpTopic;
use Interop\Amqp\Impl\AmqpTopic as ImplAmqpTopic;
use Ecavalier\Events\Amqp\TopicFactory;
use Ecavalier\Events\Tests\TestCase;

class TopicFactoryTest extends TestCase
{

    public function testMake()
    {
        $exchange = 'events';

        $context = \Mockery::mock(AmqpContext::class);
        $context->shouldReceive('createTopic')
            ->andReturn($amqpTopic = new ImplAmqpTopic($exchange));
        $context->shouldReceive()
            ->declareTopic($amqpTopic);

        $factory = new TopicFactory($context);
        $topic = $factory->make($exchange);

        self::assertSame($amqpTopic, $topic);
        self::assertEquals(AmqpTopic::TYPE_TOPIC, $topic->getType());
        self::assertEquals(AmqpTopic::FLAG_DURABLE, $topic->getFlags());
    }
}
