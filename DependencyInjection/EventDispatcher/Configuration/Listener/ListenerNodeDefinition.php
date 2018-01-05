<?php

namespace Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Listener;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;
use Webit\MessageBusBundle\DependencyInjection\Configuration\Consumer\ConsumerNodeDefinition;

class ListenerNodeDefinition extends ArrayNodeDefinition
{
    public function __construct(NodeParentInterface $parent = null)
    {
        parent::__construct('event_dispatcher', $parent);

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
            ->children()
                ->append(new EventDispatcherNodeDefinition())
                ->append(new MessageFactoriesNodeDefinition())
                ->append(new ConsumerNodeDefinition())
            ->end();
    }
}
