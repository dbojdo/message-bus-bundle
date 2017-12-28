<?php

namespace Webit\MessageBusBundle\Tests\Unit\DependencyInjection\EventDispatcher\Configuration\Listener;

use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Listener\PhpSerializeMessageFactoryNodeDefinition;
use Webit\MessageBusBundle\Tests\Unit\DependencyInjection\AbstractConfigurationNodeTestCase;

class PhpSerializeMessageFactoryNodeDefinitionTest extends AbstractConfigurationNodeTestCase
{
    protected function createTestedNode(): ParentNodeDefinitionInterface
    {
        return new PhpSerializeMessageFactoryNodeDefinition();
    }

    public static function defaultConfiguration()
    {
        return [
            'php' => array_merge(
                MessageTypeResolverNodeDefinitionTest::defaultConfiguration(),
                MessageContentProviderNodeDefinitionTest::defaultConfiguration(),
                []
            )
        ];
    }

    /**
     * @test
     */
    public function itDoesNotAddDefaultConfigurationIfNotSet()
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
    public function itSetsDefaultConfigurationIfNodeConfigured()
    {
        $config = <<<YAML
php: ~
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            self::defaultConfiguration(),
            $processedConfig
        );
    }
}