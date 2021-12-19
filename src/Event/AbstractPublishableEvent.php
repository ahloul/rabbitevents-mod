<?php

namespace RabbiteventsMod\Events\Event;

abstract class AbstractPublishableEvent implements ShouldPublish
{
    use Publishable;
}
