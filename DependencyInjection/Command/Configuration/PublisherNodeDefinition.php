<?php

namespace Webit\MessageBusBundle\DependencyInjection\Command\Configuration;

use Symfony\Component\Config\Definition\Builder\NodeParentInterface;
use Webit\MessageBusBundle\DependencyInjection\Configuration\Publisher\AbstractPublisherNodeDefinition;

class PublisherNodeDefinition extends AbstractPublisherNodeDefinition
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
