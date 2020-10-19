<?php

namespace Rmk\Adr\Event;

use Rmk\Adr\Action\ActionInterface;

trait ActionAwareEventTrait
{

    public function setAction(ActionInterface $action)
    {
        $this->setParam('action', $action);
    }

    public function getAction(): ActionInterface
    {
        return $this->getParam('action');
    }
}