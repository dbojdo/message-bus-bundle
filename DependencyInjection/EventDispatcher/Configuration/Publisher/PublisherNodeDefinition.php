<?php

namespace Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Publisher;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;

class PublisherNodeDefinition extends ArrayNodeDefinition
{
    /**
     * @param NodeParentInterface|null $parent
     */
    public function __construct(NodeParentInterface $parent = null)
    {
        parent::__construct('event_dispatcher', $parent);

        $this
            ->children()
                ->scalarNode('dispatcher')->isRequired()->cannotBeEmpty()->end()
                ->append(new EventFactoriesNodeDefinition())
            ->end();
    }
}
