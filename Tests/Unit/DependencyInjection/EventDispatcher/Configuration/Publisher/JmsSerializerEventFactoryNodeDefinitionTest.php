<?php

namespace Webit\MessageBusBundle\Tests\Unit\DependencyInjection\EventDispatcher\Configuration\Publisher;

use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Publisher\JmsSerializerEventFactoryNodeDefinition;
use Webit\MessageBusBundle\Tests\Unit\DependencyInjection\AbstractConfigurationNodeTestCase;

class JmsSerializerEventFactoryNodeDefinitionTest extends AbstractConfigurationNodeTestCase
{
    protected function createTestedNode(): ParentNodeDefinitionInterface
    {
        return new JmsSerializerEventFactoryNodeDefinition();
    }

    private static function defaultConfiguration()
    {
        return [
            'jms' => array_merge(
                EventNameResolverNodeDefinitionTest::defaultConfiguration(),
                [
                    'serializer' => 'jms_serializer',
                    'format' => 'json',
                    'types_map' => []
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

    /**
     * @test
     */
    public function itSupportsTypesMap()
    {
        $config = <<<YAML
jms:
    types_map:
        My\Event1Class: [type1, type2]
        My\Event2Class: type3
YAML;

        $expectedConfig = self::defaultConfiguration();
        $expectedConfig['jms']['types_map']['My\Event1Class'] = ['type1', 'type2'];
        $expectedConfig['jms']['types_map']['My\Event2Class'] = ['type3'];

        $processedConfig = $this->processConfig($config);

        $this->assertEquals($expectedConfig, $processedConfig);
    }
}
