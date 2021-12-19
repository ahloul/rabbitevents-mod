<?php

namespace RabbiteventsMod\Events\Tests\Queue\Stubs;

class ListenerWithAttributeMiddleware
{
    public $middleware = [
        'RabbiteventsMod\Events\Tests\Queue\Stubs\ListenerMiddleware@action',
        ListenerMiddleware::class
    ];

    public function __construct()
    {
        ListenerMiddleware::$calledTimes = 0;
    }

    public function handle($payload)
    {
        return ListenerMiddleware::$calledTimes;
    }
}
