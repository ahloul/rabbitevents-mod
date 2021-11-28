<?php

namespace Ecavalier\Events\Event;

abstract class AbstractPublishableEvent implements ShouldPublish
{
    use Publishable;
}
