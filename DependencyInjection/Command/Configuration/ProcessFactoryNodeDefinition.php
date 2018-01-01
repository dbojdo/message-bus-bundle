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
            ->cannotBeEmpty()
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('binary_path')->defaultValue('%kernel.root_dir%/../bin/console')->end()
                ->scalarNode('environment')->defaultValue('%kernel.environment%')->end()
                ->arrayNode('env_vars')
                    ->scalarPrototype()->cannotBeEmpty()->end()
                ->end()
            ->end();
    }
}