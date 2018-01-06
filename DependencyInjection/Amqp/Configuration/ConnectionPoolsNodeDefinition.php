<?php

namespace Webit\MessageBusBundle\DependencyInjection\Amqp\Configuration;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;

class ConnectionPoolsNodeDefinition extends ArrayNodeDefinition
{
    const DEFAULT_PORT = '5672';
    const DEFAULT_VHOST = '/';

    public function __construct(NodeParentInterface $parent = null)
    {
        parent::__construct('connection_pools', $parent);

        $this
            ->prototype('array')
                ->prototype('array')
                    ->children()
                        ->scalarNode('host')->cannotBeEmpty()->isRequired()->end()
                        ->scalarNode('port')->defaultValue(self::DEFAULT_PORT)->end()
                        ->scalarNode('username')->cannotBeEmpty()->isRequired()->end()
                        ->scalarNode('password')->cannotBeEmpty()->isRequired()->end()
                        ->scalarNode('vhost')->defaultValue(self::DEFAULT_VHOST)->end()
                    ->end()
                ->end()
            ->end();
    }
}