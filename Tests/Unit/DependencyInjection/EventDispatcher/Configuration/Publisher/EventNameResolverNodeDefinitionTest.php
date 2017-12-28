<?php

namespace Webit\MessageBusBundle\Tests\Unit\DependencyInjection\EventDispatcher\Configuration\Publisher;

use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Publisher\EventNameResolverNodeDefinition;
use Webit\MessageBusBundle\Tests\Unit\DependencyInjection\AbstractConfigurationNodeTestCase;

class EventNameResolverNodeDefinitionTest extends AbstractConfigurationNodeTestCase
{
    protected function createTestedNode(): ParentNodeDefinitionInterface
    {
        return new EventNameResolverNodeDefinition();
    }

    public static function defaultConfiguration()
    {
        return [
            'event_name_resolver' => [
                'type' => 'from_message_type'
            ]
        ];
    }

    /**
     * @test
     */
    public function itSetsDefaultConfigurationIfNotSet()
    {
        $config = <<<YAML
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
    public function itSetsFromMessageTypeByDefault()
    {
        $config = <<<YAML
event_name_resolver: ~
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
    public function itValidatesCustomType()
    {
        $config = <<<YAML
event_name_resolver:
    type: custom
YAML;

        $this->expectException(InvalidConfigurationException::class);
        $this->processConfig($config);
    }

    /**
     * @test
     */
    public function itIgnoresServiceKeyIfTypeOtherThenCustom()
    {
        $config = <<<YAML
event_name_resolver:
    type: from_message_type
    service: my_resolver
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            [
                'event_name_resolver' => [
                    'type' => 'from_message_type'
                ]
            ],
            $processedConfig
        );
    }

    /**
     * @test
     */
    public function itSetsTypeToCustomIfServiceConfigured()
    {
        $config = <<<YAML
event_name_resolver:
    service: my_resolver
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            [
                'event_name_resolver' => [
                    'type' => 'custom',
                    'service' => 'my_resolver'
                ]
            ],
            $processedConfig
        );
    }

    /**
     * @test
     */
    public function itSetsStringAsCustomServiceIfNonStandardTypePassed()
    {
        $config = <<<YAML
event_name_resolver: my_resolver
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            [
                'event_name_resolver' => [
                    'type' => 'custom',
                    'service' => 'my_resolver'
                ]
            ],
            $processedConfig
        );
    }

    /**
     * @test
     */
    public function itSetsStringAsStandardTypeIfStandardTypePassed()
    {
        $config = <<<YAML
event_name_resolver: from_message_type
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            [
                'event_name_resolver' => [
                    'type' => 'from_message_type'
                ]
            ],
            $processedConfig
        );
    }
}