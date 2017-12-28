<?php

namespace Webit\MessageBusBundle\Tests\Unit\DependencyInjection\Amqp\Configuration;

use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Webit\MessageBusBundle\DependencyInjection\Amqp\Configuration\ConnectionPoolsNodeDefinition;
use Webit\MessageBusBundle\Tests\Unit\DependencyInjection\AbstractConfigurationNodeTestCase;

class ConnectionPoolsNodeDefinitionTest extends AbstractConfigurationNodeTestCase
{
    protected function createTestedNode(): ParentNodeDefinitionInterface
    {
        return new ConnectionPoolsNodeDefinition();
    }

    /**
     * @test
     */
    public function itKeepsListOfConnectionPoolsAmqpConnectionNode()
    {
        $config = str_replace(
            [
                '#host11', '#port11', '#username11', '#password11', '#vhost11',
                '#host12', '#port12', '#username12', '#password12', '#vhost12',
                '#host21', '#port21', '#username21', '#password21', '#vhost21'
            ],
            [
                $host11 = $this->randomStringConfig(),
                $port11 = $this->randomStringConfig(),
                $username11 = $this->randomStringConfig(),
                $password11 = $this->randomStringConfig(),
                $vhost11 = $this->randomStringConfig(),
                $host12 = $this->randomStringConfig(),
                $port12 = $this->randomStringConfig(),
                $username12 = $this->randomStringConfig(),
                $password12 = $this->randomStringConfig(),
                $vhost12 = $this->randomStringConfig(),
                $host21 = $this->randomStringConfig(),
                $port21 = $this->randomStringConfig(),
                $username21 = $this->randomStringConfig(),
                $password21 = $this->randomStringConfig(),
                $vhost21 = $this->randomStringConfig()
            ],
            <<<YAML
connection_pools:
    pool1:
        connection11:
            host: #host11
            port: #port11
            username: #username11
            password: #password11
            vhost: #vhost11
        connection12:
            host: #host12
            port: #port12
            username: #username12
            password: #password12
            vhost: #vhost12
    pool2:
        connection21:
            host: #host21
            port: #port21
            username: #username21
            password: #password21
            vhost: #vhost21
YAML
        );

        $processedConfig = $this->processConfig($config);

        $this->assertEquals(
            [
                'connection_pools' => [
                    'pool1' => [
                        'connection11' => [
                            'host' => $host11,
                            'port' => $port11,
                            'username' => $username11,
                            'password' => $password11,
                            'vhost' => $vhost11
                        ],
                        'connection12' => [
                            'host' => $host12,
                            'port' => $port12,
                            'username' => $username12,
                            'password' => $password12,
                            'vhost' => $vhost12
                        ]
                    ],
                    'pool2' => [
                        'connection21' => [
                            'host' => $host21,
                            'port' => $port21,
                            'username' => $username21,
                            'password' => $password21,
                            'vhost' => $vhost21
                        ]
                    ]
                ]
            ],
            $processedConfig
        );
    }

    /**
     * @test
     */
    public function itSetsDefaultValueForPortAndVhost()
    {
        $config = str_replace(
            [
                '#host11', '#username11', '#password11',
            ],
            [
                $host11 = $this->randomStringConfig(),
                $username11 = $this->randomStringConfig(),
                $password11 = $this->randomStringConfig(),
            ],
            <<<YAML
connection_pools:
    pool1:
        connection11:
            host: #host11
            username: #username11
            password: #password11
YAML
        );

        $processedConfig = $this->processConfig($config);

        $this->assertEquals(
            [
                'connection_pools' => [
                    'pool1' => [
                        'connection11' => [
                            'host' => $host11,
                            'port' => ConnectionPoolsNodeDefinition::DEFAULT_PORT,
                            'username' => $username11,
                            'password' => $password11,
                            'vhost' => ConnectionPoolsNodeDefinition::DEFAULT_VHOST
                        ]
                    ]
                ]
            ],
            $processedConfig
        );
    }
}
