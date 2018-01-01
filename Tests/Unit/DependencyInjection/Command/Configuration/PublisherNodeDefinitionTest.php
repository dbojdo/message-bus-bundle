<?php

namespace Webit\MessageBusBundle\Tests\Unit\DependencyInjection\Command\Configuration;

use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Webit\MessageBusBundle\DependencyInjection\Command\Configuration\PublisherNodeDefinition;
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
    public function itConfiguresForwardToPublisher()
    {
        $config = <<<YAML
command:
    forward_to: my_publisher
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            [
                'command' => array_merge(
                    [
                        'forward_to' => 'my_publisher'
                    ],
                    AsyncNodeDefinitionTest::defaultConfiguration(),
                    ProcessFactoryNodeDefinitionTest::defaultConfiguration()
                )
            ],
            $processedConfig
        );
    }
}
