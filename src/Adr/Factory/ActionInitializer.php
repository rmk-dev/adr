<?php

namespace Rmk\Adr\Factory;

use Psr\Container\ContainerInterface;
use Rmk\Adr\Action\ActionInterface;
use Rmk\Adr\Responder\ResponderAwareInterface;
use Rmk\Adr\Exception\InternalServerException;
use Rmk\Adr\Plugin\PluginAwareInterface;
use Rmk\Adr\Responder\ResponderInterface;
use Rmk\Container\Container;
use Rmk\Router\Route;
use Rmk\ServiceContainer\ServiceContainer;

class ActionInitializer
{

    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $serviceContainer;

    /**
     * ActionInitializer constructor.
     *
     * @param ContainerInterface $serviceContainer
     */
    public function __construct(ContainerInterface $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * @param Route $route
     * @return ActionInterface
     */
    public function init(Route $route): ActionInterface
    {
        $handler = $route->getHandler();
        $action = $this->extractAction($handler);

        if (!is_string($action) && !is_callable($action)) {
            throw new InternalServerException('Invalid route configuration', 500);
        }

        if (is_string($action)) {
            $action = $this->getActionFromContainer($action);
        }

        if (!($action instanceof ActionInterface)) {
            throw new InternalServerException('Invalid action set for this route', 500);
        }

        $this->ensureAction($action);

        return $action;
    }

    /**
     * @param ActionInterface $action
     */
    protected function ensureAction(ActionInterface $action): void
    {
        if ($action instanceof ResponderAwareInterface) {
            $this->initResponderAware($action);
        }

        if ($action instanceof PluginAwareInterface) {
            $this->initPluginAware($action);
        }
    }

    /**
     * @param array|string $handler
     *
     * @return mixed
     */
    protected function extractAction($handler)
    {
        if (is_array($handler)) {
            if (array_key_exists('action', $handler)) {
                $action = $handler['action'];
            } else {
                $action = array_shift($handler);
            }
        } else {
            $action = $handler;
        }

        return $action;
    }

    /**
     * @param string $action
     *
     * @return mixed
     */
    protected function getActionFromContainer(string $action)
    {
        if (!$this->serviceContainer->has($action)) {
            throw new InternalServerException('Cannot dispatch this action', 404);
        }
        return $this->serviceContainer->get($action);
    }

    /**
     * @param ResponderAwareInterface $action
     */
    protected function initResponderAware(ResponderAwareInterface $action): void
    {
        $responderName = $action->getResponderName();
        if ($responderName) {
            $responder = $this->serviceContainer->get($responderName);
            $this->initResponder($responder);
            $action->setResponder($responder);
        }
    }

    /**
     * @param PluginAwareInterface $pluginAware
     * @param string $key
     */
    protected function initPluginAware(PluginAwareInterface $pluginAware, $key = 'action_plugins'): void
    {
        /** @var Container $config */
        $config = $this->serviceContainer->get(ServiceContainer::CONFIG_KEY);
        if ($config->has($key)) {
            $plugins = $config->get($key);
            foreach ($plugins as $pluginName) {
                $pluginAware->addPlugin($this->serviceContainer->get($pluginName));
            }
        }
    }

    /**
     * @param ResponderInterface $responder
     */
    protected function initResponder(ResponderInterface $responder): void
    {
        if ($responder instanceof PluginAwareInterface) {
            $this->initPluginAware($responder, 'responder_plugins');
        }
    }
}
