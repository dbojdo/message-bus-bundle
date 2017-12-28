<?php

namespace Webit\MessageBusBundle\Tests\Unit\DependencyInjection\EventDispatcher\Configuration\Listener;

use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Listener\MessageFactoriesNodeDefinition;
use Webit\MessageBusBundle\Tests\Unit\DependencyInjection\AbstractConfigurationNodeTestCase;

class MessageFactoriesNodeDefinitionTest extends AbstractConfigurationNodeTestCase
{
    protected function createTestedNode(): ParentNodeDefinitionInterface
    {
        return new MessageFactoriesNodeDefinition();
    }

    /**
     * @test
     */
    public function itRequiresAtLeastOnFactory()
    {
        $config = <<<YAML
message_factories: ~
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
message_factories:
    f1: ~
YAML;
        $this->expectException(InvalidConfigurationException::class);

        $this->processConfig($config);
    }

    /**
     * @test
     */
    public function itDoesNotSupportAnyMessageType()
    {
        $config = <<<YAML
message_factories:
    f1:
        php: ~
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            [
                'message_factories' => [
                    'f1' => array_merge(
                        PhpSerializeMessageFactoryNodeDefinitionTest::defaultConfiguration(),
                        [
                            'supports' => [],
                        ]
                    )
                ]
            ],
            $processedConfig
        );
    }
}
