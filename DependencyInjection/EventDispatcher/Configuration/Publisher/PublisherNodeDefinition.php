<?php

namespace Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Publisher;

use Symfony\Component\Config\Definition\Builder\NodeParentInterface;
use Webit\MessageBusBundle\DependencyInjection\Configuration\Publisher\AbstractPublisherNodeDefinition;

class PublisherNodeDefinition extends AbstractPublisherNodeDefinition
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
