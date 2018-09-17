<?php

namespace Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Listener;

use Behat\Gherkin\Node\NodeInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class EventDispatcherNodeDefinition extends ArrayNodeDefinition
{
    public function __construct(NodeInterface $parent = null)
    {
        parent::__construct('dispatcher', $parent);

        $this
            ->beforeNormalization()
                ->ifString()
                ->then(function ($node) {
                    return ['listener_tag' => $node];
                })
            ->end()
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('listener_tag')->defaultValue('kernel.event_listener')->cannotBeEmpty()->end()
            ->end();
    }
}
