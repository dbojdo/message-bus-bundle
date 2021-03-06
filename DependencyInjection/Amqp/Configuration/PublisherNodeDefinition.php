<?php

namespace Webit\MessageBusBundle\DependencyInjection\Amqp\Configuration;

use Symfony\Component\Config\Definition\Builder\NodeParentInterface;
use Webit\MessageBusBundle\DependencyInjection\Configuration\Publisher\AbstractPublisherNodeDefinition;

class PublisherNodeDefinition extends AbstractPublisherNodeDefinition
{
    /**
     * @param NodeParentInterface|null $parent
     */
    public function __construct(NodeParentInterface $parent = null)
    {
        parent::__construct('amqp', $parent);

        $this
            ->children()
                ->scalarNode('message_factory')->defaultNull()->end()
                ->arrayNode('target')->isRequired()
                    ->validate()
                        ->ifTrue(function ($node) {
                            return !isset($node['exchange']) && !isset($node['queue']);
                        })
                        ->thenInvalid('One of the keys ["exchange",  "queue"] must be set.')
                    ->end()
                    ->validate()
                        ->ifTrue(function ($node) {
                            return isset($node['exchange']) && isset($node['queue']);
                        })
                        ->thenInvalid('Only one of the keys ["exchange",  "queue"] must be set.')
                    ->end()
                    ->children()
                        ->scalarNode('pool')->isRequired()->cannotBeEmpty()->end()
                        ->arrayNode('exchange')
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($node) {
                                    return ['name' => $node];
                                })
                            ->end()
                            ->children()
                                ->scalarNode('name')->cannotBeEmpty()->isRequired()->end()
                            ->end()
                        ->end()
                        ->scalarNode('queue')->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->scalarNode('message_factory')->defaultNull()->end()
                ->scalarNode('publish_to')->cannotBeEmpty()->end()
                ->scalarNode('consumer')->cannotBeEmpty()->end()
            ->end();
    }
}
