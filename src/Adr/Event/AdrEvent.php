<?php

namespace Rmk\Adr\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rmk\Event\EventInterface;
use Rmk\Event\Traits\EventTrait;
use Rmk\Router\Route;

/**
 * Class AdrEvent
 * @package Rmk\Adr\Event
 */
class AdrEvent implements EventInterface
{
    use EventTrait;

    public function setRequest(ServerRequestInterface $request)
    {
        $this->setParam('request', $request);
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->getParam('request');
    }

    public function setResponse(ResponseInterface $response)
    {
        $this->setParam('response', $response);
    }

    public function getResponse(): ResponseInterface
    {
        return $this->getParam('response');
    }

    public function setRoute(Route $route)
    {
        $this->setParam('route', $route);
    }

    public function getRoute(): Route
    {
        return $this->getParam('route');
    }
}