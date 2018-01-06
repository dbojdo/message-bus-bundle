<?php

namespace Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Listener;

use Behat\Gherkin\Node\NodeInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class MessageFactoriesNodeDefinition extends ArrayNodeDefinition
{
    public function __construct(NodeInterface $parent = null)
    {
        parent::__construct('message_factories', $parent);

        $this
            ->isRequired()->requiresAtLeastOneElement()
            ->prototype('array')
                ->validate()
                    ->ifTrue(function ($node) {
                        return !(isset($node['jms']) || isset($node['php']) || isset($node['service']));
                    })->thenInvalid('One of the child nodes: "jms", "php", "service" must be configured.')
                ->end()
                ->children()
                    ->append(new JmsMessageFactoryNodeDefinition())
                    ->append(new PhpSerializeMessageFactoryNodeDefinition())
                    ->append(new ServiceMessageFactoryNodeDefinition())
                    ->arrayNode('supports')
                        ->prototype('scalar')->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end();
    }
}
