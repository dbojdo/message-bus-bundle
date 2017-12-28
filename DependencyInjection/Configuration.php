<?php

namespace Webit\MessageBusBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Webit\MessageBusBundle\DependencyInjection\Amqp\Configuration\AmqpNodeDefinition;
use Webit\MessageBusBundle\DependencyInjection\Configuration\ListenersNodeDefinition;
use Webit\MessageBusBundle\DependencyInjection\Configuration\PublishersNodeDefinition;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('webit_message_bundle');

        $root
            ->children()
                ->append(new AmqpNodeDefinition('amqp'))
                ->append(new ListenersNodeDefinition('listeners'))
                ->append(new PublishersNodeDefinition('publishers'))
            ->end();

        return $treeBuilder;
    }
}
