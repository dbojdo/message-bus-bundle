<?php

namespace Webit\MessageBusBundle\Tests\Unit\DependencyInjection\EventDispatcher\Configuration\Listener;

use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Listener\MessageTypeResolverNodeDefinition;
use Webit\MessageBusBundle\Tests\Unit\DependencyInjection\AbstractConfigurationNodeTestCase;

class MessageTypeResolverNodeDefinitionTest extends AbstractConfigurationNodeTestCase
{

    protected function createTestedNode(): ParentNodeDefinitionInterface
    {
        return new MessageTypeResolverNodeDefinition();
    }

    public static function defaultConfiguration()
    {
        return [
            'message_type_resolver' => [
                'type' => 'from_event_name'
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
message_type_resolver: ~
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
message_type_resolver:
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
message_type_resolver:
    type: from_event_name
    service: my_resolver
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            [
                'message_type_resolver' => [
                    'type' => 'from_event_name'
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
message_type_resolver:
    service: my_resolver
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            [
                'message_type_resolver' => [
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
message_type_resolver: my_resolver
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            [
                'message_type_resolver' => [
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
message_type_resolver: from_event_name
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            [
                'message_type_resolver' => [
                    'type' => 'from_event_name'
                ]
            ],
            $processedConfig
        );
    }
}
