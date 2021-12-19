<?php
namespace app\Providers;

class RabbitEventsServiceProvider extends \RabbiteventsMod\Events\RabbitEventsServiceProvider
{
    protected $listen = [
        'some.event' => [
            Listener::class
        ],
        'something.*' => [
            WildcardListener::class
        ],
    ];
}
