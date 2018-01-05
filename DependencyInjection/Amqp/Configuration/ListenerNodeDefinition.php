<?php

namespace Webit\MessageBusBundle\DependencyInjection\Amqp\Configuration;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;
use Webit\MessageBusBundle\DependencyInjection\Configuration\Consumer\ConsumerNodeDefinition;

class ListenerNodeDefinition extends ArrayNodeDefinition
{
    /**
     * @param NodeParentInterface|null $parent
     */
    public function __construct(NodeParentInterface $parent = null)
    {
        parent::__construct('amqp', $parent);

        $this
            ->beforeNormalization()
                ->always(function ($node) {
                    if (isset($node['forward_to'])) {
                        $node['consumer'] = ['forward_to' => $node['forward_to']];
                        unset($node['forward_to']);
                    }

                    return $node;
                })
            ->end()
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
                ->scalarNode('pool')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('queue')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('message_factory')->defaultNull()->end()
                ->append(new ConsumerNodeDefinition())
                ->arrayNode('qos')->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('prefetch_count')->defaultNull()->end()
                    ->end()
                ->end()
            ->end();
    }
}
