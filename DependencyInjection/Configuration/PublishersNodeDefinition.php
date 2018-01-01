<?php

namespace Webit\MessageBusBundle\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;
use Webit\MessageBusBundle\DependencyInjection\Amqp\Configuration\PublisherNodeDefinition as AmqpPublisherNodeDefinition;
use Webit\MessageBusBundle\DependencyInjection\Command\Configuration\PublisherNodeDefinition as CommandPublisherNodeDefinition;
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
                        return count(array_intersect(['amqp', 'event_dispatcher', 'command'], array_keys($node))) > 1;
                    })
                    ->thenInvalid('Only one of the keys ["amqp", "event_dispatcher", "command"] can be set.')
                ->end()
                ->validate()
                    ->ifTrue(function ($node) {
                        return count(array_intersect(['amqp', 'event_dispatcher', 'command'], array_keys($node))) == 0;
                    })
                    ->thenInvalid('At least one of the keys ["amqp", "event_dispatcher", "command"] must be set.')
                ->end()
                ->children()
                    ->append(new AmqpPublisherNodeDefinition())
                    ->append(new EventDispatcherPublisherNodeDefinition())
                    ->append(new CommandPublisherNodeDefinition())
                ->end()
            ->end();
    }
}
