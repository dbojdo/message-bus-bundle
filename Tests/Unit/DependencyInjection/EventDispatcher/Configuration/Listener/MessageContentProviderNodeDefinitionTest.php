<?php

namespace Webit\MessageBusBundle\Tests\Unit\DependencyInjection\EventDispatcher\Configuration\Listener;

use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Listener\MessageContentProviderNodeDefinition;
use Webit\MessageBusBundle\Tests\Unit\DependencyInjection\AbstractConfigurationNodeTestCase;

class MessageContentProviderNodeDefinitionTest extends AbstractConfigurationNodeTestCase
{

    protected function createTestedNode(): ParentNodeDefinitionInterface
    {
        return new MessageContentProviderNodeDefinition();
    }

    public static function defaultConfiguration()
    {
        return [
            'content_provider' => [
                'type' => 'event_only'
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
content_provider: ~
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
content_provider:
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
content_provider:
    type: event_only
    service: my_provider
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            [
                'content_provider' => [
                    'type' => 'event_only'
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
content_provider:
    service: my_provider
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            [
                'content_provider' => [
                    'type' => 'custom',
                    'service' => 'my_provider'
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
content_provider: my_provider
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            [
                'content_provider' => [
                    'type' => 'custom',
                    'service' => 'my_provider'
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
content_provider: event_only
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            [
                'content_provider' => [
                    'type' => 'event_only'
                ]
            ],
            $processedConfig
        );
    }
}