<?php

namespace Rmk\Adr\Plugin;

use ArrayObject;
use Rmk\Adr\Exception\UnknownPluginException;

/**
 * Trait PluginAwareTrait
 *
 * @package Rmk\Adr\Plugin
 */
trait PluginAwareTrait
{

    /**
     * @var ArrayObject
     */
    protected $plugins;

    public function initPlugins(): void
    {
        if (!($this instanceof PluginAwareInterface)) {
            throw new \RuntimeException('This trait must be used with classes that implements ' . PluginAwareInterface::class);
        }
        $this->plugins = new ArrayObject();
    }


    /**
     * @param PluginInterface $plugin
     *
     * @return mixed
     */
    public function addPlugin(PluginInterface $plugin)
    {
        if (!$this->plugins) {
            $this->initPlugins();
        }
        $plugin->pluggable($this);
        $this->plugins->offsetSet($plugin->name(), $plugin);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasPlugin(string $name): bool
    {
        return $this->plugins->offsetExists($name);
    }

    /**
     * @param string $name
     */
    public function removePlugin(string $name): void
    {
        if ($this->hasPlugin($name)) {
            $this->plugins->offsetUnset($name);
        }
    }

    /**
     * @param string $name
     *
     * @return PluginInterface
     */
    public function getPlugin(string $name): PluginInterface
    {
        if ($this->hasPlugin($name)) {
            return $this->plugins->offsetGet($name);
        }

        throw new UnknownPluginException('No such plugin ' . $name);
    }

    /**
     * @param string $name
     * @param array $args
     *
     * @return mixed
     */
    public function callPlugin(string $name, array $args = [])
    {
        return call_user_func_array([$this->getPlugin($name), 'call'], $args);
    }

    public function __call($name, $arguments)
    {
        try {
            return $this->callPlugin($name, $arguments);
        } catch (UnknownPluginException $exception) {
            throw new \BadMethodCallException('Unknown method ' . $name, 0, $exception);
        }
    }
}
