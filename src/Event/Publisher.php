<?php

namespace RabbiteventsMod\Events\Event;

use Illuminate\Support\Facades\Config;
use Interop\Amqp\Impl\AmqpMessage;
use RabbiteventsMod\Events\Queue\Context;
use JsonException;
use RabbiteventsMod\Events\Queue\Message\Factory as MessageFactory;
use RabbiteventsMod\Events\Queue\Message\Transport;

class Publisher
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Publish event to
     *
     * @param ShouldPublish $event
     *
     * @throws JsonException
     */
    public function publish(ShouldPublish $event): void
    {
        $this->transport()->send(
            MessageFactory::make($event->publishEventKey(), $event->toPublish())
        );
    }

    /**
     * @return Transport
     */
    protected function transport(): Transport
    {
        return $this->context->transport();
    }
}
