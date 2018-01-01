<?php

namespace Webit\MessageBusBundle\DependencyInjection\Command\Configuration;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;

class PublisherNodeDefinition extends ArrayNodeDefinition
{
    public function __construct(NodeParentInterface $parent = null)
    {
        parent::__construct('command', $parent);

        $this
            ->children()
                ->append(new ProcessFactoryNodeDefinition())
                ->append(new AsyncNodeDefinition())
                ->scalarNode('forward_to')->isRequired()->cannotBeEmpty()->end()
            ->end();
    }
}
