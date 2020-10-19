<?php

namespace Rmk\AdrTest;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rmk\Adr\Event\AdrEvent;
use Rmk\Router\Route;

/**
 * Class AdrEventTest
 *
 * @package Rmk\AdrTest
 */
class AdrEventTest extends TestCase
{

    public function testGettersSetters()
    {
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        $response = $this->getMockForAbstractClass(ResponseInterface::class);
        $route = $this->createStub(Route::class);
        $event = new AdrEvent();
        $event->setRequest($request);
        $event->setResponse($response);
        $event->setRoute($route);
        $this->assertSame($request, $event->getRequest());
        $this->assertSame($response, $event->getResponse());
        $this->assertSame($route, $event->getRoute());
    }
}