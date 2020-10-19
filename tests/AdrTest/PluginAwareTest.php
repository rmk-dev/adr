<?php

namespace Rmk\AdrTest;

use PHPUnit\Framework\TestCase;
use Rmk\Adr\Exception\UnknownPluginException;
use Rmk\Adr\Plugin\PluginAwareInterface;
use Rmk\Adr\Plugin\PluginAwareTrait;
use Rmk\Adr\Plugin\PluginInterface;

class PluginAwareTest extends TestCase
{

    protected $pluginAware;
    /**
     * @var PluginInterface|__anonymous@364
     */
    private $aPlugin;

    protected function setUp(): void
    {
        $this->pluginAware = new class implements PluginAwareInterface {
            use PluginAwareTrait;
        };

        $this->aPlugin = new class implements PluginInterface {
            private $pluggable;
            public function name(): string
            {
                return 'testPlugin';
            }

            public function call()
            {
                return 'Test Plugin is called!';
            }

            public function pluggable(PluginAwareInterface $pluggable = null)
            {
                if ($pluggable !== null) {
                    $this->pluggable = $pluggable;
                }

                return $this->pluggable;
            }
        };
        $this->pluginAware->addPlugin($this->aPlugin);
    }

    public function testCollectionMethods()
    {
        $this->assertSame($this->pluginAware, $this->aPlugin->pluggable());
        $this->assertTrue($this->pluginAware->hasPlugin($this->aPlugin->name()));
        $this->assertFalse($this->pluginAware->hasPlugin('not existing plugin'));
        $this->assertSame($this->aPlugin, $this->pluginAware->getPlugin($this->aPlugin->name()));
        $this->pluginAware->removePlugin($this->aPlugin->name());
        $this->assertFalse($this->pluginAware->hasPlugin($this->aPlugin->name()));
    }

    public function testCallingPlugins()
    {
        $this->assertEquals($this->aPlugin->call(), $this->pluginAware->callPlugin($this->aPlugin->name()));
        $this->assertEquals($this->aPlugin->call(), $this->pluginAware->testPlugin());
    }

    public function testCallUndefinedPlugin()
    {
        $this->expectException(UnknownPluginException::class);
        $this->pluginAware->callPlugin('undefined plugin');
    }

    public function testCallUndefinedMethod()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->pluginAware->undefinedPlugin();
    }

    public function testInvalidPluginAwareObject()
    {
        $pluginAware = new class {
            use PluginAwareTrait;
        };
        $this->expectException(\RuntimeException::class);
        $pluginAware->initPlugins();
    }
}
