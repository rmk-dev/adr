<?php

namespace Rmk\Adr;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rmk\Adr\Action\ActionInterface;
use Rmk\Adr\Event\ActionCreatedEvent;
use Rmk\Adr\Event\ActionDispatchedEvent;
use Rmk\Adr\Event\AdrEvent;
use Rmk\Adr\Event\InternalServerErrorEvent;
use Rmk\Adr\Event\RouteNotFoundEvent;
use Rmk\Adr\Factory\ActionInitializer;
use Rmk\Event\EventDispatcherAwareInterface;
use Rmk\Event\Traits\EventDispatcherAwareTrait;
use Rmk\Http\Event\HandleRequestEvent;
use Rmk\Router\NotFoundRoute;
use Rmk\Router\Route;
use Rmk\Adr\Exception\InternalServerException;
use Throwable;

/**
 * Class HandleRequestListener
 *
 * @package Rmk\Adr
 */
class HandleRequestListener implements EventDispatcherAwareInterface
{
    use EventDispatcherAwareTrait;

    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $serviceContainer;

    /**
     * @var ResponseInterface
     */
    protected ResponseInterface $response;

    /**
     * @var ServerRequestInterface
     */
    protected ServerRequestInterface $request;

    /**
     * @var Route
     */
    private Route $route;

    /**
     * HandleRequestListener constructor.
     *
     * @param ContainerInterface $serviceContainer
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ContainerInterface $serviceContainer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->serviceContainer = $serviceContainer;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Runs the ADR when the request is handled
     *
     * @param HandleRequestEvent $event
     */
    public function onHandleRequest(HandleRequestEvent $event): void
    {
        $this->response = $event->getResponse();
        $this->request = $event->getRequest();
        $this->route = $event->getMatchedRoute();
        if ($this->route instanceof NotFoundRoute) {
            $this->dispatchNotFound();
        } else {
            $this->dispatchAction();
        }
        $event->setResponse($this->response);
    }

    protected function dispatchAction(): void
    {
        try {
            $action = $this->defineAction($this->route);
            $this->response = $action($this->request);
            $event = new ActionDispatchedEvent($this, [
                'request' => $this->request,
                'response' => $this->response,
                'route' => $this->route,
                'action' => $action,
            ]);
            $this->getEventDispatcher()->dispatch($event);
            $this->populateFromEvent($event);
        } catch (Throwable $exception) {
            $this->dispatchInternalServerError($exception);
        }
    }

    protected function dispatchNotFound(Throwable $throwable = null): void
    {
        $this->response = $this->response->withStatus(404, 'Not Found');
        $event = new RouteNotFoundEvent($this, [
            'request' => $this->request,
            'response' => $this->response,
            'route' => $this->route,
            'exception' => $throwable,
        ]);
        $this->getEventDispatcher()->dispatch($event);
        $this->populateFromEvent($event);
    }

    protected function dispatchInternalServerError(Throwable $exception): void
    {
        if ($exception instanceof InternalServerException && $exception->getCode() === 404) {
            $this->dispatchNotFound($exception);
        } else {
            $this->response = $this->response->withStatus(500, 'Internal Server Error');
            $event = new InternalServerErrorEvent($this, [
                'request' => $this->request,
                'response' => $this->response,
                'route' => $this->route,
                'exception' => $exception,
            ]);
            $this->getEventDispatcher()->dispatch($event);
            $this->populateFromEvent($event);
        }
    }

    protected function defineAction(Route $route): ActionInterface
    {
        $actionInitializer = new ActionInitializer($this->getServiceContainer());
        $action = $actionInitializer->init($route);

        $event = new ActionCreatedEvent($this, [
            'request' => $this->request,
            'response' => $this->response,
            'route' => $this->route,
            'action' => $action,
        ]);
        $this->getEventDispatcher()->dispatch($event);
        $this->populateFromEvent($event);

        return $event->getAction();
    }

    /**
     * @param AdrEvent $event
     */
    protected function populateFromEvent(AdrEvent $event): void
    {
        $this->request = $event->getRequest();
        $this->response = $event->getResponse();
        $this->route = $event->getRoute();
    }

    /**
     * @return ContainerInterface
     */
    public function getServiceContainer(): ContainerInterface
    {
        return $this->serviceContainer;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return Route
     */
    public function getRoute(): Route
    {
        return $this->route;
    }
}
