<?php

namespace Webit\MessageBusBundle\DependencyInjection\Configuration\Consumer;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;

class ConsumerTypeExceptionNodeDefinition extends ArrayNodeDefinition
{
    public function __construct($name, NodeParentInterface $parent = null)
    {
        parent::__construct($name, $parent);

        $this
            ->beforeNormalization()
                ->ifString()
                ->then(function ($node) {
                    return ['service' => $node];
                })
            ->end()
            ->validate()
                ->always(function ($node) {
                    if (isset($node['service'])) {
                        unset($node['strategy']);
                        unset($node['logger']);
                    }

                    return $node;
                })
            ->end()
            ->children()
                ->scalarNode('service')->end()
                ->scalarNode('logger')->defaultNull()->end()
                ->enumNode('strategy')->values(['ignore', 'throw'])->defaultValue('throw')->end()
                ->scalarNode('forward_to')->end()
            ->end();
    }
}
