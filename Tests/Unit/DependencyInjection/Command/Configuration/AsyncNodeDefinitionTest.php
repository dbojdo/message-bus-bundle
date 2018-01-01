<?php

namespace Webit\MessageBusBundle\Tests\Unit\DependencyInjection\Command\Configuration;

use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Webit\MessageBusBundle\DependencyInjection\Command\Configuration\AsyncNodeDefinition;
use Webit\MessageBusBundle\Tests\Unit\DependencyInjection\AbstractConfigurationNodeTestCase;

class AsyncNodeDefinitionTest extends AbstractConfigurationNodeTestCase
{

    protected function createTestedNode(): ParentNodeDefinitionInterface
    {
        return new AsyncNodeDefinition();
    }

    public static function defaultConfiguration()
    {
        return [
            'async' => [
                'enabled' => false,
                'max_processes' => 5
            ]
        ];
    }

    /**
     * @test
     */
    public function itIsDisabledByDefault()
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
    public function itSetsMaxProcesses()
    {
        $config = <<<YAML
async:
    enabled: true
    max_processes: 10
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals([
                'async' => [
                    'enabled' => true,
                    'max_processes' => 10
                ]
            ],
            $processedConfig
        );
    }
}
