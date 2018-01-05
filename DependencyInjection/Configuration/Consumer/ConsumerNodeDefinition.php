<?php

namespace Webit\MessageBusBundle\DependencyInjection\Configuration\Consumer;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;

class ConsumerNodeDefinition extends ArrayNodeDefinition
{
    public function __construct(NodeParentInterface $parent = null)
    {
        parent::__construct('consumer', $parent);

        $this
            ->beforeNormalization()
                ->ifString()
                ->then(function ($node) {
                    return ['service' => $node];
                })
            ->end()
            ->beforeNormalization()
                ->always(function ($node) {
                    if (!isset($node['on_exception'])) {
                        $node['on_exception'] = null;
                    }

                    return $node;
                })
            ->end()
            ->validate()
                ->ifTrue(function ($node) {
                    return !(isset($node['service']) || isset($node['forward_to']));
                })
                ->thenInvalid('One of keys ["service", "forward_to"] must be configured.')
            ->end()
            ->children()
                ->scalarNode('service')->end()
                ->scalarNode('forward_to')->end()
                ->append(new ConsumerExceptionNodeDefinition())
            ->end();
    }
}
