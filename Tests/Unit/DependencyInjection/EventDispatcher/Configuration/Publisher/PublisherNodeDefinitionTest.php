<?php

namespace Webit\MessageBusBundle\Tests\Unit\DependencyInjection\EventDispatcher\Configuration\Publisher;

use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Publisher\PublisherNodeDefinition;
use Webit\MessageBusBundle\Tests\Unit\DependencyInjection\AbstractConfigurationNodeTestCase;

class PublisherNodeDefinitionTest extends AbstractConfigurationNodeTestCase
{

    protected function createTestedNode(): ParentNodeDefinitionInterface
    {
        return new PublisherNodeDefinition();
    }

    /**
     * @test
     */
    public function itRequiresDispatcherToBeSet()
    {
        $config = <<<YAML
event_dispatcher: ~
YAML;
        $this->expectException(InvalidConfigurationException::class);

        $this->processConfig($config);
    }

    /**
     * @test
     */
    public function itDefinesFactories()
    {
        $config = <<<YAML
event_dispatcher:
    dispatcher: my_event_dispatcher
    event_factories:
        my_factory:
            php: ~
YAML;
        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            [
                'event_dispatcher' => [
                    'dispatcher' => 'my_event_dispatcher',
                    'event_factories' => [
                        'my_factory' => array_merge(
                            PhpUnserializeEventFactoryNodeDefinitionTest::defaultConfiguration(),
                            [
                                'supports' => [],
                                'fallback' => false
                            ]
                        )
                    ]
                ]
            ],
            $processedConfig
        );
    }
}
