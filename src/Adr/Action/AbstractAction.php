<?php

namespace Rmk\Adr\Action;

use Rmk\Adr\Plugin\PluginAwareInterface;
use Rmk\Adr\Plugin\PluginAwareTrait;
use Rmk\Adr\Responder\ResponderAwareInterface;
use Rmk\Adr\Responder\ResponderInterface;

/**
 * Class AbstractAction
 *
 * @package Rmk\Adr
 */
abstract class AbstractAction implements ActionInterface, ResponderAwareInterface, PluginAwareInterface
{
    use PluginAwareTrait;

    /**
     * @var string
     */
    protected string $responderName = '';

    /**
     * @var ResponderInterface|null
     */
    protected ?ResponderInterface $responder;

    /**
     * @return string
     */
    public function getResponderName(): string
    {
        return $this->responderName;
    }

    /**
     * @return ResponderInterface|null
     */
    public function getResponder(): ?ResponderInterface
    {
        return $this->responder;
    }

    /**
     * @param ResponderInterface|null $responder
     *
     * @return AbstractAction
     */
    public function setResponder(?ResponderInterface $responder)
    {
        $this->responder = $responder;
        return $this;
    }
}
