<?php

namespace Rmk\Adr\Event;

/**
 * Class ActionCreatedEvent
 *
 * @package Rmk\Adr\Event
 */
class ActionCreatedEvent extends AdrEvent
{
    use ActionAwareEventTrait;
}