<?php

namespace AdrTest;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rmk\Adr\Action\AbstractAction;
use Rmk\Adr\Action\ActionInterface;
use Rmk\Adr\Event\ActionCreatedEvent;
use Rmk\Adr\Event\InternalServerErrorEvent;
use Rmk\Adr\Factory\HandleRequestListenerFactory;
use Rmk\Adr\HandleRequestListener;
use Rmk\Adr\Payload;
use Rmk\Adr\Plugin\PluginAwareInterface;
use Rmk\Adr\Plugin\PluginInterface;
use Rmk\Adr\Responder\AbstractResponder;
use Rmk\Container\Container;
use Rmk\Http\Event\HandleRequestEvent;
use Rmk\Http\Response;
use Rmk\Router\NotFoundRoute;
use Rmk\Router\Route;
use Rmk\ServiceContainer\ServiceContainer;

/**
 * Class HandleRequestListenerTest
 * @package AdrTest
 */
class HandleRequestListenerTest extends TestCase
{

    /**
     * @var HandleRequestListener
     */
    private $listener;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ContainerInterface
     */
    private $container;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var \PHPUnit\Framework\MockObject\Stub|HandleRequestEvent
     */
    private $event;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ServerRequestInterface
     */
    private $request;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ResponseInterface
     */
    private $response;

    /**
     * @var \PHPUnit\Framework\MockObject\Stub|Route
     */
    private $route;

    private int $code = 200;

    private string $phrase = 'OK';

    private $headers = [];

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ActionInterface
     */
    private $action;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|AbstractResponder
     */
    private $responder;

    protected function setUp(): void
    {
        $this->action = new class extends AbstractAction {
            protected string $responderName = 'test_responder';
            public function __invoke(ServerRequestInterface $request): ResponseInterface
            {
                $response = $this->getResponder()->response(new Payload(Payload::OK, $request));
                return $response->withStatus(200, 'OK')
                    ->withHeader('X-Clacks-Overhead', 'GNU Terry Pratchett');
            }
        };
        $this->dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $this->responder = $this->getMockForAbstractClass(AbstractResponder::class);
        $this->responder->method('response')->willReturnCallback(function() {
            return $this->response;
        });
        $this->container = $this->getMockForAbstractClass(ContainerInterface::class);
        $this->container->method('get')->willReturnCallback(function($name) {
            if ($name === EventDispatcherInterface::class) {
                return $this->dispatcher;
            }
            if ($name === ActionInterface::class) {
                return $this->action;
            }
            if ($name === 'test_action') {
                return static function() {};
            }
            if ($name === ServiceContainer::CONFIG_KEY) {
                return new Container([
                    'action_plugins' => ['test_plugin'],
                    'responder_plugins' => ['test_plugin'],
                ]);
            }
            if ($name === 'test_responder') {
                return $this->responder;
            }
            if ($name === 'test_plugin') {
                return new class implements PluginInterface {
                    protected PluginAwareInterface $pluginAware;
                    public function pluggable(PluginAwareInterface $pluggable = null)
                    {
                        if ($pluggable) {
                            $this->pluginAware = $pluggable;
                        }
                        return $this->pluginAware;
                    }
                    public function name(): string {return 'test_plugin'; }

                    public function call() { return 'Plugin is called'; }
                };
            }
            return null;
        });
        $this->container->method('has')->willReturnCallback(function($name) {
            $services = [
                'test_action',
                'test_responder',
                'test_plugin',
                ServiceContainer::CONFIG_KEY,
                EventDispatcherInterface::class,
                ActionInterface::class,
            ];
            return in_array($name, $services, true);
        });
        $factory = new HandleRequestListenerFactory();

        $this->listener = $factory($this->container);

        $this->request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        $this->response = $this->getMockForAbstractClass(ResponseInterface::class);
        $this->response->method('withStatus')->willReturnCallback(function ($code, $phrase = '') {
            $this->code = $code;
            $this->phrase = $phrase;
            return $this->response;
        });
        $this->response->method('getStatusCode')->willReturnCallback(function () {
            return $this->code;
        });
        $this->response->method('withHeader')->willReturnCallback(function($name, $value) {
            $this->headers[$name] = $value;
            return $this->response;
        });
        $this->response->method('getHeaderLine')->willReturnCallback(function($name) {
            return $this->headers[$name] ?? null;
        });
        $this->response->method('hasHeader')->willReturnCallback(function ($name) {
            return array_key_exists($name, $this->headers);
        });
        $this->route = $this->createStub(Route::class);
        $this->event = $this->createStub(HandleRequestEvent::class);
        $this->event->method('getResponse')->willReturnCallback(function() { return $this->response; });
        $this->event->method('getRequest')->willReturnCallback(function() { return $this->request; });
        $this->event->method('getMatchedRoute')->willReturnCallback(function() { return $this->route; });
        $this->event->method('setResponse')->willReturnCallback(function($response) { $this->response = $response; });
    }

    public function testGetters()
    {
        $this->listener->onHandleRequest($this->event);
        $this->assertSame($this->dispatcher, $this->listener->getEventDispatcher());
        $this->assertSame($this->container, $this->listener->getServiceContainer());
        $this->assertSame($this->response, $this->listener->getResponse());
        $this->assertSame($this->request, $this->listener->getRequest());
        $this->assertSame($this->route, $this->listener->getRoute());
    }

    public function testDispatchNotFoundRoute()
    {
        $this->route = $this->createStub(NotFoundRoute::class);
        $this->listener->onHandleRequest($this->event);
        $this->assertEquals(404, $this->response->getStatusCode());
    }

    public function testDefneActionInAssocArray()
    {
        $this->route->method('getHandler')->willReturn([
            'action' => 'some_action'
        ]);
        $this->listener->onHandleRequest($this->event);
        $this->assertEquals(404, $this->response->getStatusCode());
    }

    public function testDefineActionInIndexedArray()
    {
        $this->route->method('getHandler')->willReturn([1]);
        $this->listener->onHandleRequest($this->event);
        $this->assertEquals(500, $this->response->getStatusCode());
    }

    public function testGetInvalidActionFromServiceContainer()
    {
        $this->route->method('getHandler')->willReturn([
            'action' => 'test_action'
        ]);
        $this->listener->onHandleRequest($this->event);
        $this->assertEquals(500, $this->response->getStatusCode());
    }

    public function testDefineValidAction()
    {
        $this->dispatcher->method('dispatch')->willReturnCallback(function ($event) {
            if ($event instanceof InternalServerErrorEvent) {
                var_dump($event->getParams());
            }
        });
        $this->route->method('getHandler')->willReturn(ActionInterface::class);
        $this->listener->onHandleRequest($this->event);
        $this->assertSame($this->responder, $this->action->getResponder());
        $this->assertEquals(200, $this->response->getStatusCode());
        $this->assertTrue($this->response->hasHeader('X-Clacks-Overhead'));
        $this->assertEquals('GNU Terry Pratchett', $this->response->getHeaderLine('X-Clacks-Overhead'));
    }
}
