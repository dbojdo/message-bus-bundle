<?php

namespace Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Listener;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;

class ListenerNodeDefinition extends ArrayNodeDefinition
{
    public function __construct(NodeParentInterface $parent = null)
    {
        parent::__construct('event_dispatcher', $parent);

        $this
            ->validate()
                ->ifTrue(function ($node) {
                    return !isset($node['consumer']) && !isset($node['forward_to']);
                })
                ->thenInvalid('One of the keys ["consumer",  "forward_to"] must be set.')
                ->end()
            ->validate()
                ->ifTrue(function ($node) {
                    return isset($node['consumer']) && isset($node['forward_to']);
                })
                ->thenInvalid('Only one of the keys ["consumer",  "forward_to"] must be set.')
            ->end()
            ->children()
                ->append(new EventDispatcherNodeDefinition())
                ->append(new MessageFactoriesNodeDefinition())
                ->scalarNode('forward_to')->cannotBeEmpty()->end()
                ->scalarNode('consumer')->cannotBeEmpty()->end()
            ->end();
    }
}
