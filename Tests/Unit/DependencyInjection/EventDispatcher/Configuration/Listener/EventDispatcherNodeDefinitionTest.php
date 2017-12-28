<?php

namespace Webit\MessageBusBundle\Tests\Unit\DependencyInjection\EventDispatcher\Configuration\Listener;

use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Listener\EventDispatcherNodeDefinition;
use Webit\MessageBusBundle\Tests\Unit\DependencyInjection\AbstractConfigurationNodeTestCase;

class EventDispatcherNodeDefinitionTest extends AbstractConfigurationNodeTestCase
{
    protected function createTestedNode(): ParentNodeDefinitionInterface
    {
        return new EventDispatcherNodeDefinition();
    }

    public static function defaultConfiguration()
    {
        return [
            'dispatcher' => [
                'listener_tag' => 'kernel.event_listener'
            ]
        ];
    }

    /**
     * @test
     */
    public function itAddsDefaultConfigurationIfNotSet()
    {
        $config = <<<YAML
YAML;
        $processedConfig = $this->processConfig($config);
        $this->assertEquals(self::defaultConfiguration(), $processedConfig);
    }

    /**
     * @test
     */
    public function itSetsDefaultConfiguration()
    {
        $config = <<<YAML
dispatcher: ~
YAML;
        $processedConfig = $this->processConfig($config);
        $this->assertEquals(self::defaultConfiguration(), $processedConfig);
    }

    /**
     * @test
     */
    public function itSetsSupportsStringAsListenerTag()
    {
        $config = <<<YAML
dispatcher: my_listener_tag
YAML;
        $processedConfig = $this->processConfig($config);
        $this->assertEquals([
            'dispatcher' => [
                'listener_tag' => 'my_listener_tag'
            ]
        ], $processedConfig);
    }
}
