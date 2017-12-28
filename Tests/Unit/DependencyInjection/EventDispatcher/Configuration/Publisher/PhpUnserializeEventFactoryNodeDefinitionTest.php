<?php

namespace Webit\MessageBusBundle\Tests\Unit\DependencyInjection\EventDispatcher\Configuration\Publisher;

use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Publisher\PhpUnserializeEventFactoryNodeDefinition;
use Webit\MessageBusBundle\Tests\Unit\DependencyInjection\AbstractConfigurationNodeTestCase;

class PhpUnserializeEventFactoryNodeDefinitionTest extends AbstractConfigurationNodeTestCase
{

    protected function createTestedNode(): ParentNodeDefinitionInterface
    {
        return new PhpUnserializeEventFactoryNodeDefinition();
    }

    public static function defaultConfiguration()
    {
        return [
            'php' => array_merge(
                EventNameResolverNodeDefinitionTest::defaultConfiguration(),
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
