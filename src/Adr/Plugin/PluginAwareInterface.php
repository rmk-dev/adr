<?php

namespace Rmk\Adr\Plugin;

/**
 * Interface PluginAwareInterface
 *
 * @package Rmk\Adr\Plugin
 */
interface PluginAwareInterface
{

    /**
     * @param PluginInterface $plugin
     *
     * @return mixed
     */
    public function addPlugin(PluginInterface $plugin);

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasPlugin(string $name): bool;

    /**
     * @param string $name
     *
     * @return bool
     */
    public function removePlugin(string $name): void;

    /**
     * @param string $name
     *
     * @return PluginInterface
     */
    public function getPlugin(string $name): PluginInterface;

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function callPlugin(string $name);
}