<?php

namespace Webit\MessageBusBundle\Tests\Unit\DependencyInjection\EventDispatcher\Configuration\Listener;

use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Listener\JmsMessageFactoryNodeDefinition;
use Webit\MessageBusBundle\Tests\Unit\DependencyInjection\AbstractConfigurationNodeTestCase;

class JmsMessageFactoryNodeDefinitionTest extends AbstractConfigurationNodeTestCase
{

    protected function createTestedNode(): ParentNodeDefinitionInterface
    {
        return new JmsMessageFactoryNodeDefinition();
    }

    private static function defaultConfiguration()
    {
        return [
            'jms' => array_merge(
                MessageTypeResolverNodeDefinitionTest::defaultConfiguration(),
                MessageContentProviderNodeDefinitionTest::defaultConfiguration(),
                [
                    'serializer' => 'jms_serializer',
                    'format' => 'json'
                ]
            )
        ];
    }

    /**
     * @test
     */
    public function itDoesNotAddDefaultConfiguration()
    {
        $config = <<<YAML
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            [],
            $processedConfig
        );
    }

    /**
     * @test
     */
    public function itSetsDefaultConfiguration()
    {
        $config = <<<YAML
jms: ~
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            self::defaultConfiguration(),
            $processedConfig
        );
    }
}
