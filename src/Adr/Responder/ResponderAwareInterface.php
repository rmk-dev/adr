<?php

namespace Rmk\Adr\Responder;

use Rmk\Adr\Responder\ResponderInterface;

/**
 * Class ResponderAwareInterface
 * @package Rmk\Adr\Action
 */
interface ResponderAwareInterface
{

    /**
     * @return string
     */
    public function getResponderName(): string;

    /**
     * @return ResponderInterface|null
     */
    public function getResponder(): ?ResponderInterface;

    /**
     * @param ResponderInterface|null $responder
     */
    public function setResponder(?ResponderInterface $responder);
}