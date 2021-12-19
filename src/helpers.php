<?php

use Illuminate\Support\Arr;
use Illuminate\Container\Container;
use RabbiteventsMod\Events\Event\Publisher;
use RabbiteventsMod\Events\Event\ShouldPublish;

if (!function_exists('publish')) {

    function publish($event, array $payload = [])
    {
        if (is_string($event)) {
            $event = new class($event, $payload) implements ShouldPublish {
                private $event;
                private $payload;

                public function __construct(string $event, array $payload = [])
                {
                    $this->event = $event;
                    $this->payload = Arr::isAssoc($payload) ? [$payload] : Arr::wrap($payload);
                }

                public function publishEventKey(): string
                {
                    $serviceName="Laravel";
                    if(function_exists('config')){
                        $serviceName=config('rabbitevents.connections.rabbitmq.rabbitevents_service_name');
                    }
                    return $serviceName.":".$this->event;
                }

                public function toPublish(): array
                {
                    return $this->payload;
                }
            };
        }

        Container::getInstance()
            ->make(Publisher::class)
            ->publish($event);
    }

}
