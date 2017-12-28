<?php

namespace Webit\MessageBusBundle\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;
use Webit\MessageBusBundle\DependencyInjection\Amqp\Configuration\PublisherNodeDefinition as AmqpPublisherNodeDefinition;
use Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Publisher\PublisherNodeDefinition as EventDispatcherPublisherNodeDefinition;

class PublishersNodeDefinition extends ArrayNodeDefinition
{
    public function __construct($name, NodeParentInterface $parent = null)
    {
        parent::__construct($name, $parent);

        $this
            ->arrayPrototype()
                ->validate()
                    ->ifTrue(function ($node) {
                        return isset($node['amqp']) && isset($node['event_dispatcher']);
                    })
                    ->thenInvalid('Only one of the keys ["amqp", "event_dispatcher"] can be set.')
                ->end()
                ->children()
                    ->append(new AmqpPublisherNodeDefinition())
                    ->append(new EventDispatcherPublisherNodeDefinition())
                ->end()
            ->end();
    }
}
