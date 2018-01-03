<?php

namespace Webit\MessageBusBundle\Tests\Unit\DependencyInjection\Command\Configuration;

use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Webit\MessageBusBundle\DependencyInjection\Command\Configuration\ProcessFactoryNodeDefinition;
use Webit\MessageBusBundle\Tests\Unit\DependencyInjection\AbstractConfigurationNodeTestCase;

class ProcessFactoryNodeDefinitionTest extends AbstractConfigurationNodeTestCase
{
    protected function createTestedNode(): ParentNodeDefinitionInterface
    {
        return new ProcessFactoryNodeDefinition();
    }

    public static function defaultConfiguration()
    {
        return [
            'process_factory' => [
                'binary_path' => '%kernel.root_dir%/../bin/console',
                'command' => 'webit_message_bus:publish',
                'environment' => '%kernel.environment%',
                'env_vars' => []
            ]
        ];
    }

    /**
     * @test
     */
    public function itAddsDefaultConfigurationIfNotSet()
    {
        $config = <<<YAML
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            self::defaultConfiguration(),
            $processedConfig
        );
    }
}
