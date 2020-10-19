<?php

namespace Rmk\AdrTest;

use PHPUnit\Framework\TestCase;
use Rmk\Adr\Action\ActionInterface;
use Rmk\Adr\Event\ActionAwareEventTrait;
use Rmk\Event\Traits\EventTrait;

class ActionAwareEventTraitTest extends TestCase
{

    public function testGettersSetters()
    {
        $action = $this->getMockForAbstractClass(ActionInterface::class);
        $event = new class {
            use ActionAwareEventTrait;
            use EventTrait;
        };
        $event->setAction($action);
        $this->assertSame($action, $event->getAction());
    }
}