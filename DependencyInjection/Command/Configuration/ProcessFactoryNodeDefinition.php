<?php

namespace Webit\MessageBusBundle\DependencyInjection\Command\Configuration;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;

class ProcessFactoryNodeDefinition extends ArrayNodeDefinition
{
    public function __construct(NodeParentInterface $parent = null)
    {
        parent::__construct('process_factory', $parent);

        $this
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('binary_path')->defaultValue('%kernel.root_dir%/../bin/console')->end()
                ->scalarNode('command')->defaultValue('webit_message_bus:publish')->end()
                ->scalarNode('environment')->defaultValue('%kernel.environment%')->end()
                ->arrayNode('env_vars')
                    ->prototype('scalar')->cannotBeEmpty()->end()
                ->end()
            ->end();
    }
}