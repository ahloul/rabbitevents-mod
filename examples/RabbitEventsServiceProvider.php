<?php
namespace app\Providers;

class RabbitEventsServiceProvider extends \Ecavalier\Events\RabbitEventsServiceProvider
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
