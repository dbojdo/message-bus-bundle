<?php

namespace Webit\MessageBusBundle\Tests\Unit\DependencyInjection\EventDispatcher\Configuration\Publisher;

use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Publisher\EventFactoriesNodeDefinition;
use Webit\MessageBusBundle\Tests\Unit\DependencyInjection\AbstractConfigurationNodeTestCase;

class EventFactoriesNodeDefinitionTest extends AbstractConfigurationNodeTestCase
{

    protected function createTestedNode(): ParentNodeDefinitionInterface
    {
        return new EventFactoriesNodeDefinition();
    }

    /**
     * @test
     */
    public function itRequiresAtLeastOnFactory()
    {
        $config = <<<YAML
event_factories: ~
YAML;
        $this->expectException(InvalidConfigurationException::class);

        $this->processConfig($config);
    }

    /**
     * @test
     */
    public function itRequiresOneTypeToBeConfigured()
    {
        $config = <<<YAML
event_factories:
    f1: ~
YAML;
        $this->expectException(InvalidConfigurationException::class);

        $this->processConfig($config);
    }

    /**
     * @test
     */
    public function itDoesNotSupportAnyMessageTypeAndIsNotFallbackByDefault()
    {
        $config = <<<YAML
event_factories:
    f1:
        php: ~
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            [
                'event_factories' => [
                    'f1' => array_merge(
                        PhpUnserializeEventFactoryNodeDefinitionTest::defaultConfiguration(),
                        [
                            'supports' => [],
                            'fallback' => false
                        ]
                    )
                ]
            ],
            $processedConfig
        );
    }

    /**
     * @test
     */
    public function itAllowsOnlyOneFallbackFactory()
    {
        $config = <<<YAML
event_factories:
    f1:
        php: ~
        fallback: true
    f2:
        php: ~
        fallback: true
        
YAML;
        $this->expectException(InvalidConfigurationException::class);
        $this->processConfig($config);
    }
}
