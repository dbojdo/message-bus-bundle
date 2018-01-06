<?php

namespace Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Publisher;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;

class JmsSerializerEventFactoryNodeDefinition extends ArrayNodeDefinition
{
    /**
     * @param NodeParentInterface|null $parent
     */
    public function __construct(NodeParentInterface $parent = null)
    {
        parent::__construct('jms', $parent);

        $this
            ->children()
                ->append(new EventNameResolverNodeDefinition())
                ->scalarNode('serializer')->defaultValue('jms_serializer')->cannotBeEmpty()->end()
                ->enumNode('format')->values(['json', 'xml'])->defaultValue('json')->cannotBeEmpty()->end()
                ->arrayNode('types_map')
                    ->prototype('array')
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function ($node) {
                                return (array)$node;
                            })
                        ->end()
                        ->prototype('scalar')->end()
                        ->requiresAtLeastOneElement()
                    ->end()
                ->end()
            ->end();
    }
}