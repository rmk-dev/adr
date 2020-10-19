<?php

namespace Rmk\Adr\Plugin;

/**
 * Interface PluginInterface
 *
 * @package Rmk\Adr\Plugin
 */
interface PluginInterface
{

    /**
     * Sets or gets the pluggable object (The one that the plugin is attached to)
     *
     * @param PluginAwareInterface|null $pluggable
     *
     * @return mixed
     */
    public function pluggable(PluginAwareInterface $pluggable = null);

    public function name(): string;

    public function call();
}