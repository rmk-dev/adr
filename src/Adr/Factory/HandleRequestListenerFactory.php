<?php

namespace Rmk\Adr\Factory;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Rmk\Adr\HandleRequestListener;
use Rmk\ServiceContainer\FactoryInterface;
use Rmk\ServiceContainer\ServiceContainerInterface;

/**
 * Class HandleRequestListenerFactory
 *
 * @package Rmk\Adr\Factory
 */
class HandleRequestListenerFactory implements FactoryInterface
{

    /**
     * Creates and returns the service
     *
     * @param ContainerInterface $serviceContainer The service container
     * @param string|null $serviceName The service name
     *
     * @return mixed
     */
    public function __invoke(ContainerInterface $serviceContainer, $serviceName = null)
    {
        $eventDispatcher = $serviceContainer->get(EventDispatcherInterface::class);

        return new HandleRequestListener($serviceContainer, $eventDispatcher);
    }
}