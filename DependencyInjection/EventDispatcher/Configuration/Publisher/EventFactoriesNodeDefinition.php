<?php

namespace Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Publisher;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\NodeInterface;

class EventFactoriesNodeDefinition extends ArrayNodeDefinition
{
    public function __construct(NodeInterface $parent = null)
    {
        parent::__construct('event_factories', $parent);

        $this
            ->isRequired()->requiresAtLeastOneElement()
            ->validate()
                ->ifTrue(function ($node) {
                    $hasFallback = false;
                    foreach ($node as $factory) {
                        if ($hasFallback && isset($factory['fallback']) && $factory['fallback']) {
                            return true;
                        }
                        $hasFallback = $factory['fallback'];
                    }

                    return false;
                })
                ->thenInvalid('Only one factory can be marked as "fallback".')
            ->end()
            ->prototype('array')
                ->validate()
                    ->ifTrue(function ($node) {
                        return !(isset($node['jms']) || isset($node['php']) || isset($node['service']));
                    })->thenInvalid('One of the child nodes: "jms", "php", "service" must be configured.')
                ->end()
                ->children()
                    ->append(new JmsSerializerEventFactoryNodeDefinition())
                    ->append(new PhpUnserializeEventFactoryNodeDefinition())
                    ->append(new ServiceEventFactoryNodeDefinition())
                    ->arrayNode('supports')
                        ->prototype('scalar')->cannotBeEmpty()->end()
                    ->end()
                    ->booleanNode('fallback')->defaultValue(false)->end()
                ->end()
            ->end();
    }
}
