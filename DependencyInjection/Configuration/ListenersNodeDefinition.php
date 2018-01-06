<?php

namespace Webit\MessageBusBundle\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;
use Webit\MessageBusBundle\DependencyInjection\Amqp\Configuration\ListenerNodeDefinition as AmqpListenerNodeDefinition;
use Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Listener\ListenerNodeDefinition as EventDispatcherListenerNodeDefinition;

class ListenersNodeDefinition extends ArrayNodeDefinition
{
    public function __construct($name, NodeParentInterface $parent = null)
    {
        parent::__construct($name, $parent);

        $this
            ->prototype('array')
                ->validate()
                    ->ifTrue(function ($node) {
                        return isset($node['amqp']) && isset($node['event_dispatcher']);
                    })
                    ->thenInvalid('Only one of the keys ["amqp", "event_dispatcher"] can be set.')
                ->end()
                ->children()
                    ->scalarNode('register_as')->defaultNull()->end()
                    ->append(new AmqpListenerNodeDefinition())
                    ->append(new EventDispatcherListenerNodeDefinition())
                ->end()
            ->end();
    }
}
