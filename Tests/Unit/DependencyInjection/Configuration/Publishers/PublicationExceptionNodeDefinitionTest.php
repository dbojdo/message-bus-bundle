<?php

namespace Webit\MessageBusBundle\Tests\Unit\DependencyInjection\Configuration\Publishers;

use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Webit\MessageBusBundle\DependencyInjection\Configuration\Publisher\PublicationExceptionNodeDefinition;
use Webit\MessageBusBundle\Tests\Unit\DependencyInjection\AbstractConfigurationNodeTestCase;

class PublicationExceptionNodeDefinitionTest extends AbstractConfigurationNodeTestCase
{

    protected function createTestedNode(): ParentNodeDefinitionInterface
    {
        return new PublicationExceptionNodeDefinition();
    }

    public static function defaultConfiguration()
    {
        return [
            'on_exception' => [
                'unsupported_message' => [
                    'strategy' => 'throw',
                    'logger' => 'logger',
                ],
                'default' => [
                    'strategy' => 'throw',
                    'logger' => 'logger',
                ]
            ]
        ];
    }

    /**
     * @test
     */
    public function itProvidesDefaultValues()
    {
        $config = <<<YAML
on_exception: ~
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
    public function itAllowsToSetServiceAtRootLevel()
    {
        $config = <<<YAML
on_exception:
    service: my_exception_handler
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            [
                'on_exception' => [
                    'unsupported_message' => [
                        'service' => 'my_exception_handler'
                    ],
                    'default' => [
                        'service' => 'my_exception_handler'
                    ]
                ]
            ],
            $processedConfig
        );
    }

    /**
     * @test
     */
    public function itAllowsToSetServiceAsString()
    {
        $config = <<<YAML
on_exception: my_exception_handler
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            [
                'on_exception' => [
                    'unsupported_message' => [
                        'service' => 'my_exception_handler'
                    ],
                    'default' => [
                        'service' => 'my_exception_handler'
                    ]
                ]
            ],
            $processedConfig
        );
    }

    /**
     * @test
     */
    public function itConfiguresHandlerByType()
    {
        $config = <<<YAML
on_exception:
    unsupported_message:
        service: my_handler
YAML;

        $processedConfig = $this->processConfig($config);
        $this->assertEquals(
            [
                'on_exception' => [
                    'unsupported_message' => [
                        'service' => 'my_handler'
                    ],
                    'default' => [
                        'strategy' => 'throw',
                        'logger' => 'logger',
                    ]
                ]
            ],
            $processedConfig
        );
    }
}
